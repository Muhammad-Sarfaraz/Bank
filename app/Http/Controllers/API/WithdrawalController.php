<?php

namespace App\Http\Controllers\API;

use App\Contracts\WithdrawalRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\WithdrawalResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    private $withdrawalRepositoryInterface;

    public function __construct(WithdrawalRepositoryInterface $withdrawalRepositoryInterface)
    {
        $this->withdrawalRepositoryInterface = $withdrawalRepositoryInterface;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $data = Transaction::query()
            ->where('user_id', $user->id)
            ->where('transaction_type', Transaction::WITHDRAWAL)
            ->get();

        return new WithdrawalResource($data);
    }

    public function store(WithdrawRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                return $this->withdrawalRepositoryInterface->processWithdrawal($request->amount);
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());

            return response()->json(['error' => 'An error occurred while processing the withdrawal.'], 500);
        }
    }
}
