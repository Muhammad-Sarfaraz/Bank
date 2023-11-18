<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::query()
            ->where('user_id', $user->id)
            ->get();

        $data = [
            'balance' => $user->balance,
            'transactions' => $transactions,
        ];

        return new TransactionResource($data);
    }
}
