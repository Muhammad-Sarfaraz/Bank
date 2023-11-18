<?php

namespace Tests\Feature\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\WithdrawalRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use DatabaseTransactions;

    public function testAsFridayForIndividual()
    {
        Carbon::setTestNow(Carbon::parse('2023-11-24'));
        $user = User::factory()->create(['balance' => 50000, 'account_type' => 'individual']);
        Auth::login($user);

        $withdrawalRepository = new WithdrawalRepository();
        $withdrawalRepository->processWithdrawal(10000);

        $this->assertEquals(40000, $user->fresh()->balance);
        Carbon::setTestNow();
    }

    public function testProcessesWithdrawalForIndividual()
    {
        $user = User::factory()->create(['balance' => 1500, 'account_type' => 'individual']);
        Auth::login($user);

        $withdrawalRepository = new WithdrawalRepository();
        $withdrawalRepository->processWithdrawal(1000);

        $this->assertEquals(500, $user->fresh()->balance);
    }

    public function testIndividualAccountBenefit()
    {
        $user = User::factory()->create(['balance' => 25000, 'account_type' => 'individual']);
        Auth::login($user);

        // First 5k is free for month.
        $withdrawalRepository = new WithdrawalRepository();
        $withdrawalRepository->processWithdrawal(5000);

        $this->assertEquals(20000, $user->fresh()->balance);

        // Here I have 20k now.
        $fee = (10000 - 1000) * (Transaction::INDIVIDUAL_ACCOUNT_WITHDRAWAL_FEE / 100);
        $withdrawalRepository->processWithdrawal(10000);

        $this->assertEquals((20000 - (10000 + $fee)), $user->fresh()->balance, 0.001);
    }

    public function testProcessesWithdrawalForBusiness()
    {
        $user = User::factory()->create(['balance' => 100000, 'account_type' => 'business']);
        Auth::login($user);

        $fee = 10000 * (Transaction::BUSINESS_ACCOUNT_WITHDRAWAL_FEE / 100);

        $withdrawalRepository = new WithdrawalRepository();
        $withdrawalRepository->processWithdrawal(10000);

        $total = 100000 - (10000 + $fee);

        $this->assertEquals($total, $user->fresh()->balance, 0.001);
    }

    public function testReduceFeeForBusinessAfterFiftyThousand()
    {
        $user = User::factory()->create(['balance' => 100000, 'account_type' => 'business']);
        Auth::login($user);

        // After 50k.
        $feeForTenThousand = 10000 * (Transaction::INDIVIDUAL_ACCOUNT_WITHDRAWAL_FEE / 100);
        $feeForFiftyThousand = 50000 * (Transaction::BUSINESS_ACCOUNT_WITHDRAWAL_FEE / 100);

        $withdrawalRepository = new WithdrawalRepository();
        $withdrawalRepository->processWithdrawal(50000);
        $withdrawalRepository->processWithdrawal(10000);

        $total = 100000 - (10000 + 50000 + $feeForTenThousand + $feeForFiftyThousand);

        $this->assertEquals($total, $user->fresh()->balance, 0.001);
    }
}
