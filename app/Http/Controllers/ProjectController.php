<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateWebsiteJob;
use App\Models\Generation;
use App\Models\Project;
use App\Services\CreditService;
use App\Services\InsufficientCreditsException;
use App\Services\OpenRouterModelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('projects/index', [
            'projects' => $user->projects()
                ->latest()
                ->get(['id', 'name', 'prompt', 'status', 'created_at'])
                ->toArray(),
            'creditsBalance' => $user->credits_balance,
            'generations' => $user->generations()
                ->whereNotIn('status', [Generation::STATUS_COMPLETED, Generation::STATUS_FAILED])
                ->get(['id', 'project_id', 'status', 'progress'])
                ->toArray(),
        ]);
    }

    public function models(OpenRouterModelService $service): JsonResponse
    {
        return response()->json($service->fetchFreeModels());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:10', 'max:2000'],
            'model' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        if ($user->is_suspended) {
            return back()->with('error', 'Your account is suspended.');
        }

        // Credit deduction is enforced server-side before any AI work is queued.
        try {
            app(CreditService::class)->deduct(
                $user,
                config('ai-website.generation_cost', 1),
                'generation',
                'Website generation'
            );
        } catch (InsufficientCreditsException) {
            return back()->with('error', 'You are out of credits. Upgrade your plan to keep generating.');
        }

        $project = $user->projects()->create([
            'name' => '',
            'prompt' => $validated['prompt'],
            'status' => 'queued',
        ]);

        $generation = $project->generations()->create([
            'user_id' => $user->id,
            'prompt' => $validated['prompt'],
            'status' => Generation::STATUS_QUEUED,
            'credits_spent' => config('ai-website.generation_cost', 1),
            'model' => $validated['model'] ?? null,
        ]);

        GenerateWebsiteJob::dispatch($generation);

        return to_route('projects.show', $project);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_unless($project->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $project->load(['currentVersion:id,project_id,version,code']);

        $generation = $project->generations()->latest()->first(['id', 'status', 'progress', 'error', 'credits_spent']);

        return Inertia::render('projects/show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'prompt' => $project->prompt,
                'status' => $project->status,
                'spec' => $project->spec,
            ],
            'generation' => $generation?->toArray(),
            'creditsBalance' => $request->user()->credits_balance,
        ]);
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $projectPath = "sites/{$project->id}";
        if (Storage::disk('local')->exists($projectPath)) {
            Storage::disk('local')->deleteDirectory($projectPath);
        }

        $project->delete();

        return to_route('projects.index');
    }
}
