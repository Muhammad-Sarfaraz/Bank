<?php

namespace App\Repositories;

use App\Contracts\WithdrawalRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    protected $withdrawalFee = 0;

    public function processWithdrawal($withdrawalAmount)
    {
        $user = Auth::user();

        $this->validateWithdrawalAmount($withdrawalAmount);

        $this->setWithdrawalFee($user, $withdrawalAmount);

        $totalDeduction = $withdrawalAmount + $this->withdrawalFee;

        if ($user->balance < $totalDeduction) {
            return response()->json(['error' => 'Insufficient balance for withdrawal.'], 422);
        }

        $this->updateUserBalance($user, $totalDeduction);
        $this->createWithdrawalTransaction($user, $withdrawalAmount);

        return response()->json(['message' => 'Withdrawal successful'], 200);
    }

    protected function validateWithdrawalAmount($withdrawalAmount)
    {
        $validator = Validator::make(['amount' => $withdrawalAmount], [
            'amount' => 'required|numeric|min:1|max:999999999',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid withdrawal amount');
        }
    }

    protected function setWithdrawalFee($user, $withdrawalAmount)
    {
        $userWiseFee = $user->account_type == Transaction::INDIVIDUAL_ACCOUNT ?
            Transaction::INDIVIDUAL_ACCOUNT_WITHDRAWAL_FEE : Transaction::BUSINESS_ACCOUNT_WITHDRAWAL_FEE;

        if ($user->account_type == Transaction::INDIVIDUAL_ACCOUNT) {
            $this->setIndividualAccountWithdrawalFee($withdrawalAmount, $userWiseFee);
        } else {

            $this->setBusinessAccountWithdrawalFee($user, $withdrawalAmount, $userWiseFee);
        }
    }

    protected function setIndividualAccountWithdrawalFee($withdrawalAmount, $userWiseFee)
    {
        $isFriday = Carbon::now()->isFriday();

        if (! $isFriday && $withdrawalAmount > 1000) {
            $amountForFee = $withdrawalAmount - 1000;
            $this->withdrawalFee = $amountForFee * ($userWiseFee / 100);
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $totalWithdrawalsWithinLimit = Transaction::query()
            ->where('user_id', Auth::user()->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('transaction_type', 'withdrawal')
            ->sum('amount');

        if (($totalWithdrawalsWithinLimit + $withdrawalAmount) <= 5000) {
            $this->withdrawalFee = 0;
        }

    }

    protected function setBusinessAccountWithdrawalFee($user, $withdrawalAmount, $userWiseFee)
    {
        $totalWithdrawal = Transaction::query()
            ->where('transaction_type', 'withdrawal')
            ->where('user_id', $user->id)
            ->sum('amount');

        if ($totalWithdrawal >= 50000) {
            $userWiseFee = 0.015;
        }

        $this->withdrawalFee = $withdrawalAmount * ($userWiseFee / 100);
    }

    protected function updateUserBalance($user, $totalDeduction)
    {
        $user->balance -= $totalDeduction;
        $user->save();
    }

    protected function createWithdrawalTransaction($user, $withdrawalAmount)
    {
        return Transaction::create([
            'user_id' => $user->id,
            'transaction_type' => Transaction::WITHDRAWAL,
            'amount' => $withdrawalAmount,
            'fee' => $this->withdrawalFee,
            'date' => now(),
        ]);
    }
}
