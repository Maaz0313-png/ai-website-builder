<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_pages(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/plans')->assertOk();
    }

    public function test_regular_users_cannot_access_admin_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();

        $this->actingAs($user)->put('/admin/users/'.$user->id, ['is_suspended' => true])->assertForbidden();
    }

    public function test_guests_cannot_access_admin_pages(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_admin_can_adjust_user_credits_with_ledger_entry(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $target = User::factory()->create(['credits_balance' => 5]);

        $this->actingAs($admin)->put("/admin/users/{$target->id}", ['credits_delta' => 20])
            ->assertRedirect();

        $this->assertEquals(25, $target->fresh()->credits_balance);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $target->id,
            'reason' => 'admin_adjustment',
            'amount' => 20,
        ]);
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->from('/admin/users')
            ->put("/admin/users/{$admin->id}", ['is_suspended' => true])
            ->assertSessionHas('error');

        $this->assertFalse($admin->fresh()->is_suspended);
    }

    public function test_admin_cannot_create_negative_balance(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $target = User::factory()->create(['credits_balance' => 3]);

        $this->actingAs($admin)->from('/admin/users')
            ->put("/admin/users/{$target->id}", ['credits_delta' => -10])
            ->assertSessionHas('error');

        $this->assertEquals(3, $target->fresh()->credits_balance);
    }

    public function test_admin_can_update_plan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $plan = \App\Models\Plan::factory()->create(['monthly_credits' => 10]);

        $this->actingAs($admin)->put("/admin/plans/{$plan->id}", ['monthly_credits' => 99])
            ->assertRedirect();

        $this->assertEquals(99, $plan->fresh()->monthly_credits);
    }
}
