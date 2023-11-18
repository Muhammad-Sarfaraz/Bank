<?php

namespace App\Http\Controllers\API;

use App\Contracts\DepositRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Resources\DepositResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    private $depositRepositoryInterface;

    public function __construct(DepositRepositoryInterface $depositRepositoryInterface)
    {
        $this->depositRepositoryInterface = $depositRepositoryInterface;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $data = Transaction::query()
            ->where('user_id', $user->id)
            ->where('transaction_type', Transaction::DEPOSIT)
            ->get();

        return new DepositResource($data);
    }

    public function store(DepositRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $this->depositRepositoryInterface->processDeposit($request->amount);

                return response()->json(['message' => 'Deposit successful'], 200);
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => 'An error occurred while processing the deposit.'], 500);
        }
    }
}
