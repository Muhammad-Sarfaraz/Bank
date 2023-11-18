<?php

namespace App\Contracts;

interface WithdrawalRepositoryInterface
{
    public function processWithdrawal($amount);
}
