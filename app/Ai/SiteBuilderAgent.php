<?php

namespace App\Ai;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class SiteBuilderAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        protected string $prompt,
        protected string $existingSpecJson = '',
        protected ?string $model = null,
    ) {}

    /**
     * Failover chain of free OpenRouter models — laravel/ai hops
     * to the next entry on rate limits or outages.
     */
    public function provider(): array
    {
        if ($this->model) {
            return [
                'openrouter' => $this->model,
                'openrouter_backup' => config('ai-website.openrouter_backup_model', 'minimax/minimax-m3:free'),
            ];
        }

        return [
            'openrouter' => config('ai-website.openrouter_model', 'z-ai/glm-5.2:free'),
            'openrouter_backup' => config('ai-website.openrouter_backup_model', 'minimax/minimax-m3:free'),
        ];
    }

    public static function make(string $prompt, string $existingSpecJson = '', ?string $model = null): self
    {
        return new self($prompt, $existingSpecJson, $model);
    }

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are an expert website architect. You convert a user's natural-language
            description into a website specification (site spec).

            The site spec must contain:
            - "name": a short, catchy name for the website.
            - "description": one sentence describing the site.
            - "theme": { "primary_color": hex color, "accent_color": hex color, "font": one of "sans", "serif", "mono" }.
            - "pages": 1 to 5 pages. Each page has:
              - "title", "slug" (lowercase-with-dashes),
              - "sections": 1 to 6 sections per page. Each section has:
                - "type": one of "hero", "features", "about", "gallery", "testimonials", "pricing", "contact", "cta".
                - "heading": section headline.
                - "body": 1-3 sentences of realistic copy for the site's subject.
                - "items": optional list of { "title", "body" } used by features/pricing/testimonials/gallery sections.

            Write real, specific copy tailored to the prompt — never lorem ipsum or placeholders.
            Return only the structured site spec.
            INSTRUCTIONS;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'description' => $schema->string()->required(),
            'theme' => $schema->object([
                'primary_color' => $schema->string()->required(),
                'accent_color' => $schema->string()->required(),
                'font' => $schema->string()->required(),
            ])->required(),
            'pages' => $schema->array()->items(
                $schema->object([
                    'title' => $schema->string()->required(),
                    'slug' => $schema->string()->required(),
                    'sections' => $schema->array()->items(
                        $schema->object([
                            'type' => $schema->string()->required(),
                            'heading' => $schema->string()->required(),
                            'body' => $schema->string()->required(),
                            'items' => $schema->array()->items(
                                $schema->object([
                                    'title' => $schema->string(),
                                    'body' => $schema->string(),
                                ])
                            ),
                        ])
                    )->required(),
                ])
            )->required(),
        ];
    }

    public function userPrompt(): string
    {
        if ($this->existingSpecJson !== '') {
            return <<<PROMPT
                Here is the current site spec:

                {$this->existingSpecJson}

                Apply this change request and return the complete updated spec:
                {$this->prompt}
                PROMPT;
        }

        return "Create a website based on this description:\n\n{$this->prompt}";
    }
}
