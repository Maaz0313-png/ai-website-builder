<?php

namespace App\Jobs;

use App\Events\GenerationProgressUpdated;
use App\Models\ProjectVersion;
use App\Services\SiteSourceWriter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CompileSiteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public ProjectVersion $version,
    ) {}

    public function handle(): void
    {
        $version = $this->version->fresh();

        // Idempotency: skip if already compiled.
        if ($version === null || $version->build_path !== null) {
            return;
        }

        $sourceDir = app(SiteSourceWriter::class)->write($version);

        $response = Http::withToken(config('ai-website.compile_token'))
            ->timeout(config('ai-website.compile_timeout', 120))
            ->post(config('ai-website.compile_service_url').'/compile', [
                'project_id' => $version->project_id,
                'version_id' => $version->id,
            ]);

        if ($response->failed() || ($response->json('ok') !== true)) {
            throw new \RuntimeException(
                'Compile service failed: '.substr($response->json('error') ?? $response->body(), 0, 500)
            );
        }

        $relativeBuildPath = $response->json('output_dir'); // e.g. sites/{pid}/build/{vid}
        $absolute = storage_path('app/'.$relativeBuildPath);
        if (! is_dir($absolute)) {
            throw new \RuntimeException('Compile output missing on disk.');
        }

        $version->update(['build_path' => $relativeBuildPath]);

        if ($version->generation_id && ($generation = $version->generation()->first())) {
            try {
                GenerationProgressUpdated::dispatch($generation);
            } catch (Throwable) {
            }
        }
    }
}
