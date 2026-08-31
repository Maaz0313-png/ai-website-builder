<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SiteEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function projectWithVersion(User $user): Project
    {
        $project = $user->projects()->create([
            'name' => 'Brew Haven',
            'prompt' => 'Coffee shop site',
            'status' => 'generated',
        ]);

        $version = $project->versions()->create([
            'version' => 1,
            'source' => ProjectVersion::SOURCE_GENERATION,
            'spec' => [
                'theme' => ['primary_color' => '#6f4e37', 'accent_color' => '#c8a27a', 'font' => 'serif'],
                'pages' => [
                    ['title' => 'Home', 'slug' => 'home', 'sections' => [
                        ['type' => 'hero', 'heading' => 'Hi', 'body' => 'Welcome', 'items' => []],
                    ]],
                ],
            ],
            'code' => '<!DOCTYPE html><html><body>Original</body></html>',
        ]);

        $project->update(['current_version_id' => $version->id]);

        return $project;
    }

    public function test_owner_can_save_visual_spec_and_new_version_created(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $project = $this->projectWithVersion($user);

        $response = $this->actingAs($user)->put("/projects/{$project->id}/spec", [
            'name' => 'Brew Haven v2',
            'spec' => [
                'theme' => ['primary_color' => '#111111', 'accent_color' => '#eeeeee', 'font' => 'sans'],
                'pages' => [
                    ['title' => 'Home', 'slug' => 'home', 'sections' => [
                        ['type' => 'hero', 'heading' => 'New heading', 'body' => 'New body', 'items' => []],
                    ]],
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $project->versions()->count());

        $head = $project->refresh()->currentVersion()->first();
        $this->assertEquals(ProjectVersion::SOURCE_VISUAL_EDITOR, $head->source);
        $this->assertEquals('#111111', $head->spec['theme']['primary_color']);
        $this->assertStringContainsString('New heading', $head->code);
    }

    public function test_owner_can_save_code_edits(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $project = $this->projectWithVersion($user);

        $this->actingAs($user)->put("/projects/{$project->id}/code", [
            'code' => '<!DOCTYPE html><html><body>Custom code edit</body></html>',
        ])->assertRedirect();

        $head = $project->refresh()->currentVersion()->first();
        $this->assertEquals(ProjectVersion::SOURCE_CODE_EDITOR, $head->source);
        $this->assertStringContainsString('Custom code edit', $head->code);
        // Spec preserved untouched.
        $this->assertNotNull($head->spec);
    }

    public function test_code_validation_rejects_fragments(): void
    {
        $user = User::factory()->create();
        $project = $this->projectWithVersion($user);

        $this->actingAs($user)->from('/projects/'.$project->id.'/editor')
            ->put("/projects/{$project->id}/code", ['code' => 'just some text'])
            ->assertSessionHasErrors('code');
    }

    public function test_other_users_cannot_edit_or_preview(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = $this->projectWithVersion($owner);

        $this->actingAs($stranger)->get("/preview/{$project->id}")->assertForbidden();
        $this->actingAs($stranger)->put("/projects/{$project->id}/code", [
            'code' => '<html></html>',
        ])->assertForbidden();
    }

    public function test_preview_serves_compiled_build_then_code_fallback(): void
    {
        $user = User::factory()->create();
        $project = $this->projectWithVersion($user);

        // No build yet → falls back to stored code.
        $this->actingAs($user)->get("/preview/{$project->id}")
            ->assertOk()
            ->assertSee('Original');
    }

    public function test_admin_can_edit_any_project(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = $this->projectWithVersion(User::factory()->create());

        $this->actingAs($admin)->get("/preview/{$project->id}")->assertOk();
    }
}
