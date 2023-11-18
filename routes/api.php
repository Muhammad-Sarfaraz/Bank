<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DepositController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WithdrawalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/deposit', [DepositController::class, 'index']);
    Route::post('/deposit', [DepositController::class, 'store']);

    Route::get('/withdrawal', [WithdrawalController::class, 'index']);
    Route::post('/withdrawal', [WithdrawalController::class, 'store']);

    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('users', [AuthController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
