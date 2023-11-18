<?php

namespace App\Contracts;

interface DepositRepositoryInterface
{
    public function processDeposit($amount);
}
