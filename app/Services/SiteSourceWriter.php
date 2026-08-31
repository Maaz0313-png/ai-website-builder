<?php

namespace App\Services;

use App\Models\ProjectVersion;
use Illuminate\Support\Facades\Storage;

class SiteSourceWriter
{
    /**
     * Write a project version's source files to the shared workspace
     * that the Node compile service reads from.
     *
     * @return string relative path within storage/app/sites
     */
    public function write(ProjectVersion $version): string
    {
        $project = $version->project;
        $base = "{$project->id}/src/{$version->id}";
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/sites'),
        ]);

        $spec = $version->spec ?? [];
        $pages = $spec['pages'] ?? [];

        if (empty($pages)) {
            throw new \RuntimeException('Cannot write site source: spec has no pages.');
        }

        foreach ($pages as $page) {
            $slug = $this->slug($page['slug'] ?? ($page['title'] ?? 'home'));
            $code = app(SiteRenderer::class)->renderPage($spec, $page['slug'] ?? $slug);
            $disk->put("{$base}/{$slug}.html", $code);
        }

        return $base;
    }

    protected function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim((string) preg_replace('/-+/', '-', $value), '-');

        return $value !== '' ? $value : 'home';
    }
}
