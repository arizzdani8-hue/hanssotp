<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    public function credit(User $user, float $amount, string $type, string $description, ?string $referenceType = null, ?int $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceType, $referenceId) {
            $user = User::lockForUpdate()->find($user->id);
            $balanceBefore = (float) $user->balance;
            $balanceAfter = $balanceBefore + $amount;

            $user->update(['balance' => $balanceAfter]);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    public function debit(User $user, float $amount, string $type, string $description, ?string $referenceType = null, ?int $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceType, $referenceId) {
            $user = User::lockForUpdate()->find($user->id);
            $balanceBefore = (float) $user->balance;

            if ($balanceBefore < $amount) {
                throw new \Exception('Saldo tidak mencukupi');
            }

            $balanceAfter = $balanceBefore - $amount;
            $user->update(['balance' => $balanceAfter]);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }
}
