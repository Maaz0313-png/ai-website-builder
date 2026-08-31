<?php

namespace Tests\Feature;

use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;
use App\Services\CreditService;
use App\Services\InsufficientCreditsException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_grant_increases_balance_and_writes_ledger(): void
    {
        $user = User::factory()->create(['credits_balance' => 5]);
        $service = app(CreditService::class);

        $service->grant($user, 10, 'signup_grant');

        $this->assertEquals(15, $user->fresh()->credits_balance);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'amount' => 10,
            'balance_after' => 15,
            'reason' => 'signup_grant',
        ]);
    }

    public function test_deduct_decreases_balance_and_writes_ledger(): void
    {
        $user = User::factory()->create(['credits_balance' => 10]);
        $generation = Generation::create([
            'user_id' => $user->id,
            'project_id' => \App\Models\Project::create([
                'user_id' => $user->id,
                'name' => 'Test',
                'prompt' => 'test prompt',
            ])->id,
            'prompt' => 'test prompt',
        ]);

        app(CreditService::class)->deduct($user, 4, 'generation', null, $generation);

        $this->assertEquals(6, $user->fresh()->credits_balance);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'generation_id' => $generation->id,
            'amount' => -4,
            'balance_after' => 6,
        ]);
    }

    public function test_deduct_throws_and_leaves_balance_unchanged_when_insufficient(): void
    {
        $user = User::factory()->create(['credits_balance' => 3]);

        try {
            app(CreditService::class)->deduct($user, 5, 'generation');
            $this->fail('Expected InsufficientCreditsException was not thrown.');
        } catch (InsufficientCreditsException) {
        }

        $this->assertEquals(3, $user->fresh()->credits_balance);
        $this->assertEquals(0, CreditTransaction::count());
    }

    public function test_deduct_rejects_zero_or_negative_amounts(): void
    {
        $user = User::factory()->create(['credits_balance' => 10]);

        $this->expectException(\InvalidArgumentException::class);
        app(CreditService::class)->deduct($user, 0, 'generation');
    }
}
