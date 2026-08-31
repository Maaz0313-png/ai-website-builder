<?php

namespace App\Jobs;

use App\Ai\SiteBuilderAgent;
use App\Events\GenerationProgressUpdated;
use App\Models\Generation;
use App\Models\ProjectVersion;
use App\Services\CreditService;
use App\Services\SiteRenderer;use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateWebsiteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public Generation $generation,
    ) {}

    public function handle(SiteRenderer $renderer): void
    {
        $generation = $this->generation->fresh();

        if ($generation === null || $generation->status === Generation::STATUS_COMPLETED) {
            return;
        }

        $generation->update([
            'status' => Generation::STATUS_PROCESSING,
            'started_at' => now(),
            'progress' => 5,
            'meta' => array_merge($generation->meta ?? [], ['step' => 'Initializing generation...']),
        ]);

        $this->broadcast($generation);

        try {
            $agent = SiteBuilderAgent::make(
                prompt: $generation->prompt,
                existingSpecJson: (string) ($generation->project->spec ? json_encode($generation->project->spec) : ''),
                model: $generation->model ?? null,
            );

            $generation->update([
                'progress' => 15,
                'meta' => array_merge($generation->meta ?? [], ['step' => 'Sending prompt to AI model...']),
            ]);
            $this->broadcast($generation);

            $response = $agent->prompt($agent->userPrompt());

/** @var array $spec */
$spec = method_exists($response, 'toArray') && ! empty($response->structured ?? [])
    ? $response->structured
    : json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);

            $generation->update([
                'progress' => 40,
                'meta' => array_merge($generation->meta ?? [], ['step' => 'Processing AI response...']),
            ]);
            $this->broadcast($generation);

            /** @var array $spec */
            $spec = method_exists($response, 'toArray') && ! empty($response->structured ?? [])
                ? $response->structured
                : json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);

            if (empty($spec['pages'])) {
                throw new \RuntimeException('Agent returned a spec without pages.');
            }

            $generation->update([
                'progress' => 55,
                'meta' => array_merge($generation->meta ?? [], ['step' => 'Validating site specification...']),
            ]);
            $this->broadcast($generation);

            $code = $renderer->renderPage($spec, $spec['pages'][0]['slug'] ?? 'home');

            $generation->update([
                'progress' => 70,
                'meta' => array_merge($generation->meta ?? [], ['step' => 'Creating project version...']),
            ]);
            $this->broadcast($generation);

            $version = ProjectVersion::create([
                'project_id' => $generation->project_id,
                'generation_id' => $generation->id,
                'version' => ((int) $generation->project->versions()->max('version')) + 1,
                'source' => ProjectVersion::SOURCE_GENERATION,
                'spec' => $spec,
                'code' => $code,
            ]);

            $generation->project->update([
                'spec' => $spec,
                'status' => 'generated',
                'current_version_id' => $version->id,
                'name' => $generation->project->name ?: ($spec['name'] ?? $generation->project->name),
            ]);

            $meta = $generation->meta ?? [];
            try {
                $meta['provider'] = $response?->meta?->provider ?? null;
                $meta['model'] = $response?->meta?->model ?? null;
                $meta['step'] = 'Generation complete';
            } catch (Throwable) {
            }

            $generation->update([
                'status' => Generation::STATUS_COMPLETED,
                'progress' => 100,
                'finished_at' => now(),
                'provider' => $meta['provider'] ?? null,
                'model' => $meta['model'] ?? null,
                'meta' => $meta,
            ]);

            $this->broadcast($generation);

            // Hand off to the internal Node.js compile worker.
            CompileSiteJob::dispatch($version);
        } catch (Throwable $e) {
            $generation->update([
                'status' => Generation::STATUS_FAILED,
                'error' => substr($e->getMessage(), 0, 2000),
                'finished_at' => now(),
                'meta' => array_merge($generation->meta ?? [], ['step' => 'Failed: ' . $e->getMessage()]),
            ]);

            // Refund the credit that was deducted up front.
            app(CreditService::class)->grant(
                $generation->user,
                max($generation->credits_spent, 1),
                'generation_refund',
                'Refund for failed generation #'.$generation->id,
                $generation
            );

            $this->broadcast($generation);

            throw $e;
        }
    }

    protected function broadcast(Generation $generation): void
    {
        try {
            GenerationProgressUpdated::dispatch($generation);
        } catch (Throwable $e) {
            // Never let broadcast transport failures fail a generation.
            logger()->warning('Generation broadcast failed: '.$e->getMessage());
        }
    }
}
