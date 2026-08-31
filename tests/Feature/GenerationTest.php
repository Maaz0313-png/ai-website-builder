<?php

namespace Tests\Feature;

use App\Ai\SiteBuilderAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Ai;
use Tests\TestCase;

class GenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function validSpec(): array
    {
        return [
            'name' => 'Brew Haven',
            'description' => 'A cozy coffee shop site.',
            'theme' => ['primary_color' => '#6f4e37', 'accent_color' => '#c8a27a', 'font' => 'serif'],
            'pages' => [
                [
                    'title' => 'Home',
                    'slug' => 'home',
                    'sections' => [
                        ['type' => 'hero', 'heading' => 'Welcome to Brew Haven', 'body' => 'Small-batch coffee, big flavor.', 'items' => []],
                        [
                            'type' => 'features',
                            'heading' => 'Why us',
                            'body' => 'What makes us special.',
                            'items' => [
                                ['title' => 'Local roasts', 'body' => 'Roasted in-house daily.'],
                                ['title' => 'Cozy space', 'body' => 'Perfect for work or relaxing.'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'About',
                    'slug' => 'about',
                    'sections' => [
                        ['type' => 'about', 'heading' => 'Our story', 'body' => 'Family owned since 2015.', 'items' => []],
                    ],
                ],
            ],
        ];
    }

    public function test_user_with_credits_can_generate_a_website(): void
    {
        Queue::fake();

        $user = User::factory()->create(['credits_balance' => 5]);

        $response = $this->actingAs($user)->post('/projects', [
            'prompt' => 'A landing page for a cozy coffee shop called Brew Haven',
        ]);

        $project = $user->projects()->firstOrFail();
        $response->assertRedirect(route('projects.show', $project, absolute: false));

        // Credit deducted server-side up front.
        $this->assertEquals(4, $user->fresh()->credits_balance);

        Queue::assertPushed(\App\Jobs\GenerateWebsiteJob::class);
    }

    public function test_generation_without_credits_is_rejected(): void
    {
        $user = User::factory()->create(['credits_balance' => 0]);

        $this->actingAs($user)->from('/projects')->post('/projects', [
            'prompt' => 'A website for my portfolio',
        ])->assertSessionHas('error');

        $this->assertEquals(0, \App\Models\Project::count());
        $this->assertEquals(0, $user->fresh()->generations()->count());
    }

    public function test_prompt_validation_enforced(): void
    {
        $user = User::factory()->create(['credits_balance' => 10]);

        $this->actingAs($user)->post('/projects', ['prompt' => 'short'])
            ->assertSessionHasErrors('prompt');
    }

    public function test_generate_website_job_creates_spec_and_version(): void
    {
        Ai::fakeAgent(SiteBuilderAgent::class, [$this->validSpec()]);

        $user = User::factory()->create(['credits_balance' => 1]);
        $project = $user->projects()->create([
            'name' => '',
            'prompt' => 'Coffee shop site',
            'status' => 'queued',
        ]);
        $generation = $project->generations()->create([
            'user_id' => $user->id,
            'prompt' => 'Coffee shop site',
            'status' => 'queued',
            'credits_spent' => 1,
        ]);

        (new \App\Jobs\GenerateWebsiteJob($generation))->handle(new \App\Services\SiteRenderer());

        $project->refresh();
        $this->assertEquals('generated', $project->status);
        $this->assertCount(2, $project->spec['pages']);
        $this->assertNotNull($project->current_version_id);
        $this->assertStringContainsString('<!DOCTYPE html>', $project->currentVersion->code);
        $this->assertDatabaseHas('generations', ['id' => $generation->id, 'status' => 'completed']);
    }

    public function test_failed_generation_refunds_credit(): void
    {
        Ai::fakeAgent(SiteBuilderAgent::class, [function () {
            throw new \RuntimeException('Provider outage');
        }]);

        $user = User::factory()->create(['credits_balance' => 0]);
        $project = $user->projects()->create(['name' => '', 'prompt' => 'x prompt long enough', 'status' => 'queued']);
        $generation = $project->generations()->create([
            'user_id' => $user->id,
            'prompt' => 'Coffee shop site',
            'status' => 'queued',
            'credits_spent' => 1,
        ]);

        try {
            (new \App\Jobs\GenerateWebsiteJob($generation))->handle(new \App\Services\SiteRenderer());
            $this->fail('Expected exception');
        } catch (\RuntimeException) {
        }

        $this->assertDatabaseHas('generations', ['id' => $generation->id, 'status' => 'failed']);
        $this->assertEquals(1, $user->fresh()->credits_balance);
        $this->assertDatabaseHas('credit_transactions', ['user_id' => $user->id, 'reason' => 'generation_refund']);
    }
}
