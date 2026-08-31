<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Grant credits to a user and record a ledger entry.
     */
    public function grant(User $user, int $amount, string $reason, ?string $description = null, ?Generation $generation = null): CreditTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Grant amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $description, $generation) {
            $user = User::whereKey($user->getKey())->lockForUpdate()->findOrFail($user->getKey());

            $user->credits_balance += $amount;
            $user->save();

            return CreditTransaction::create([
                'user_id' => $user->id,
                'generation_id' => $generation?->id,
                'amount' => $amount,
                'balance_after' => $user->credits_balance,
                'reason' => $reason,
                'description' => $description,
            ]);
        });
    }

    /**
     * Deduct credits from a user, enforcing a non-negative balance server-side.
     *
     * @throws InsufficientCreditsException
     */
    public function deduct(User $user, int $amount, string $reason, ?string $description = null, ?Generation $generation = null): CreditTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduction amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $description, $generation) {
            $user = User::whereKey($user->getKey())->lockForUpdate()->findOrFail($user->getKey());

            if ($user->credits_balance < $amount) {
                throw new InsufficientCreditsException(
                    "Insufficient credits: required {$amount}, available {$user->credits_balance}."
                );
            }

            $user->credits_balance -= $amount;
            $user->save();

            return CreditTransaction::create([
                'user_id' => $user->id,
                'generation_id' => $generation?->id,
                'amount' => -$amount,
                'balance_after' => $user->credits_balance,
                'reason' => $reason,
                'description' => $description,
            ]);
        });
    }
}
