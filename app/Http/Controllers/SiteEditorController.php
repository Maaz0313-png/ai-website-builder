<?php

namespace App\Http\Controllers;

use App\Jobs\CompileSiteJob;
use App\Models\Project;
use App\Models\ProjectVersion;
use App\Services\SiteRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class SiteEditorController extends Controller
{
    public function edit(Request $request, Project $project)
    {
        abort_unless($project->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return Inertia::render('projects/editor', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'spec' => $project->spec,
            ],
            'code' => $project->currentVersion?->code ?? '',
        ]);
    }

    /**
     * Visual editor save: updates the spec and regenerates code deterministically.
     */
    public function updateSpec(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'spec' => ['required', 'array'],
            'spec.theme' => ['required', 'array'],
            'spec.theme.primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'spec.theme.accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'spec.theme.font' => ['required', 'in:sans,serif,mono'],
            'spec.pages' => ['required', 'array', 'min:1', 'max:10'],
            'spec.pages.*.title' => ['required', 'string', 'max:120'],
            'spec.pages.*.slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'spec.pages.*.sections' => ['present', 'array'],
            'spec.pages.*.sections.*.type' => ['required', 'in:hero,features,about,gallery,testimonials,pricing,contact,cta'],
            'spec.pages.*.sections.*.heading' => ['required', 'string', 'max:200'],
            'spec.pages.*.sections.*.body' => ['required', 'string', 'max:2000'],
            'spec.pages.*.sections.*.items' => ['present', 'nullable', 'array'],
        ]);

        $version = $this->newVersion($project, ProjectVersion::SOURCE_VISUAL_EDITOR, function () use ($validated) {
            return [
                'spec' => $validated['spec'],
                'code' => app(SiteRenderer::class)->renderPage(
                    $validated['spec'],
                    $validated['spec']['pages'][0]['slug'] ?? 'home'
                ),
            ];
        });

        $project->update(['name' => $validated['name'], 'spec' => $validated['spec'], 'current_version_id' => $version->id]);

        CompileSiteJob::dispatch($version);

        return back()->with('success', 'Changes saved and queued for build.');
    }

    /**
     * Code editor save: persists raw code edits as the new head version.
     * The spec is preserved untouched — see README note on last-write semantics.
     */
    public function updateCode(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:500000'],
        ]);

        if (! str_contains(strtolower($validated['code']), '<html') && ! str_contains(strtolower($validated['code']), '<!doctype')) {
            return back()->withErrors(['code' => 'Edited code must be a full HTML document.']);
        }

        $version = $this->newVersion($project, ProjectVersion::SOURCE_CODE_EDITOR, function () use ($project, $validated) {
            return [
                'spec' => $project->spec ?? $project->currentVersion?->spec,
                'code' => $validated['code'],
            ];
        });

        $project->update(['current_version_id' => $version->id]);

        CompileSiteJob::dispatch($version);

        return back()->with('success', 'Code saved and queued for build.');
    }

    protected function newVersion(Project $project, string $source, callable $attributes): ProjectVersion
    {
        $data = $attributes();

        return ProjectVersion::create([
            ...$data,
            'project_id' => $project->id,
            'generation_id' => null,
            'version' => ((int) $project->versions()->max('version')) + 1,
            'source' => $source,
        ]);
    }

    /**
     * Serve compiled site files for the live preview iframe.
     */
    public function preview(Request $request, Project $project, ?string $path = null): Response
    {
        abort_unless($project->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $path = $path ?: '';
        // Reject any traversal attempts.
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(400);
        }

        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app')]);
        $buildPath = $project->currentVersion?->build_path;

        if ($buildPath !== null && $disk->exists("{$buildPath}/{$path}") && is_file(storage_path("app/{$buildPath}/{$path}"))) {
            $contents = $disk->get("{$buildPath}/{$path}");
        } else {
            $code = $project->currentVersion?->code;
            abort_if(empty($code), 404, 'No compiled preview available yet.');
            $contents = $code;
        }

        $mimeType = str_ends_with($path, '.css') ? 'text/css'
            : (str_ends_with($path, '.js') ? 'application/javascript' : 'text/html');

        return response($contents, 200, ['Content-Type' => "{$mimeType}; charset=UTF-8"]);
    }
}
