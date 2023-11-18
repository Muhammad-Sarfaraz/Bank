<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_type',
        'user_id',
        'amount',
        'fee',
        'date',
    ];

    public const INDIVIDUAL_ACCOUNT = 'individual';

    public const BUSINESS_ACCOUNT = 'business';

    public const DEPOSIT = 'deposit';

    public const WITHDRAWAL = 'withdrawal';

    public const BUSINESS_ACCOUNT_WITHDRAWAL_FEE = 0.025;

    public const INDIVIDUAL_ACCOUNT_WITHDRAWAL_FEE = 0.015;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
