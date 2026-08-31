<?php

namespace App\Services;

use Illuminate\Support\Str;

class SiteRenderer
{
    /**
     * Render a full standalone HTML document for a single page of the site spec.
     */
    public function renderPage(array $spec, string $slug): string
    {
        $page = $this->pageFor($spec, $slug);

        $theme = $spec['theme'] ?? [];
        $primary = $this->color($theme['primary_color'] ?? '#4f46e5');
        $accent = $this->color($theme['accent_color'] ?? '#06b6d4');
        $font = in_array($theme['font'] ?? '', ['sans', 'serif', 'mono']) ? $theme['font'] : 'sans';
        $fontStack = match ($font) {
            'serif' => "Georgia, 'Times New Roman', serif",
            'mono' => "'SF Mono', Menlo, Consolas, monospace",
            default => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif",
        };

        $nav = '';
        foreach (($spec['pages'] ?? []) as $p) {
            $href = '/preview/'.urlencode($spec['name'] ?? 'site').'/'.$this->slug($p['slug'] ?? ($p['title'] ?? '')).'.html';
            $nav .= '<a href="'.$this->e($href).'">'.$this->e($p['title'] ?? 'Page').'</a>';
        }

        $body = '';
        foreach (($page['sections'] ?? []) as $section) {
            $body .= $this->renderSection($section);
        }

        $name = $spec['name'] ?? 'Website';

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$this->e($page['title'] ?? $name)} — {$this->e($name)}</title>
        <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:{$fontStack};color:#1f2937;line-height:1.6;background:#fff}
        nav{display:flex;gap:24px;padding:20px 32px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:#fff;z-index:10}
        nav .brand{font-weight:700;color:{$primary};margin-right:auto;font-size:1.1rem;text-decoration:none}
        nav a{text-decoration:none;color:#374151;font-size:.95rem}
        nav a:hover{color:{$primary}}
        section{padding:72px 32px;max-width:1080px;margin:0 auto}
        h1{font-size:2.75rem;line-height:1.15;margin-bottom:16px}
        h2{font-size:1.9rem;margin-bottom:12px}
        p.body{color:#4b5563;max-width:640px;font-size:1.05rem}
        .hero{background:linear-gradient(135deg, {$primary}12, {$accent}22)}
        .hero h1 span{color:{$primary}}
        .grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin-top:36px}
        .card{border:1px solid #e5e7eb;border-radius:12px;padding:24px}
        .card h3{margin-bottom:8px;font-size:1.1rem}
        .card p{color:#4b5563;font-size:.95rem}
        .btn{display:inline-block;margin-top:28px;background:{$primary};color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600}
        footer{padding:40px 32px;border-top:1px solid #e5e7eb;color:#6b7280;text-align:center;font-size:.9rem}
        </style>
        </head>
        <body>
        <nav><a class="brand" href="#">{$this->e($name)}</a>{$nav}</nav>
        {$body}
        <footer>© {$this->e($name)} — All rights reserved.</footer>
        </body>
        </html>
        HTML;
    }

    protected function renderSection(array $section): string
    {
        $type = $section['type'] ?? 'about';
        $heading = $this->e($section['heading'] ?? '');
        $body = $this->e($section['body'] ?? '');
        $items = is_array($section['items'] ?? null) ? $section['items'] : [];

        return match ($type) {
            'hero' => <<<HTML
            <section class="hero">
                <h1>{$heading}</h1>
                <p class="body">{$body}</p>
                <a class="btn" href="#get-started">Get Started</a>
            </section>
            HTML,
            'features', 'pricing', 'testimonials', 'gallery' => $this->renderCardsSection($type, $heading, $body, $items),
            default => <<<HTML
            <section id="{$this->e($type)}">
                <h2>{$heading}</h2>
                <p class="body">{$body}</p>
            </section>
            HTML,
        };
    }

    protected function renderCardsSection(string $type, string $heading, string $body, array $items): string
    {
        $cards = '';
        foreach ($items as $item) {
            $title = $this->e($item['title'] ?? '');
            $text = $this->e($item['body'] ?? '');
            $cards .= "<div class=\"card\"><h3>{$title}</h3><p>{$text}</p></div>";
        }
        if ($cards === '' && $body !== '') {
            $cards = "<div class=\"card\"><h3></h3><p></p></div>";
        }

        return <<<HTML
        <section id="{$this->e($type)}">
            <h2>{$heading}</h2>
            <p class="body">{$body}</p>
            <div class="grid">{$cards}</div>
        </section>
        HTML;
    }

    protected function pageFor(array $spec, string $slug): array
    {
        foreach (($spec['pages'] ?? []) as $page) {
            if ($this->slug($page['slug'] ?? ($page['title'] ?? '')) === $slug) {
                return $page;
            }
        }

        return $spec['pages'][0] ?? ['title' => 'Home', 'sections' => []];
    }

    protected function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim((string) preg_replace('/-+/', '-', $value), '-');

        return $value !== '' ? $value : Str::random(6);
    }

    protected function color(string $value): string
    {
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : '#4f46e5';
    }

    protected function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
