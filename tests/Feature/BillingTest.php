<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_page_shows_plans_and_credit_balance(): void
    {
        Plan::factory()->create(['slug' => 'free', 'monthly_credits' => 10]);

        $user = User::factory()->create(['credits_balance' => 42]);

        $response = $this->actingAs($user)->get('/billing');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('billing/index')
                ->where('creditsBalance', 42)
                ->has('plans', 1)
        );
    }

    public function test_registration_grants_free_plan_credits(): void
    {
        Plan::factory()->create(['slug' => 'free', 'monthly_credits' => 25]);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::whereEmail('new@example.com')->firstOrFail();
        $this->assertEquals(25, $user->credits_balance);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'reason' => 'signup_grant',
            'amount' => 25,
        ]);
    }

    public function test_subscribe_validates_plan_slug(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/billing')
            ->post('/billing/subscribe', ['plan' => 'nonexistent']);

        $response->assertSessionHasErrors('plan');
    }
}
