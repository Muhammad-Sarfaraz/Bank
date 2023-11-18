<?php

namespace App\Repositories;

use App\Contracts\DepositRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DepositRepository implements DepositRepositoryInterface
{
    public function processDeposit($amount)
    {
        request()->validate([
            'amount' => 'required|numeric|min:1|max:999999999',
        ]);

        $user = Auth::user();
        $newBalance = $user->balance + $amount;

        $user->update([
            'balance' => $newBalance,
        ]);

        return Transaction::create([
            'user_id' => $user->id,
            'transaction_type' => Transaction::DEPOSIT,
            'amount' => $amount,
            'date' => now(),
        ]);
    }
}
