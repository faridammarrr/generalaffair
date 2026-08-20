<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $fillable = [
        'name',
        'request_date',
        'letter_number',
        'requestor_name',
        'division',
        'budget_id',
        'budget_name',
        'receiver_name',
        'payment_due_date',
        'payment_method',
        'bank_account_number',
        'amount',
        'type',
        'status',
        'submitted_at',
        'paid_at'
    ];

    protected $casts = [
        'request_date' => 'date',
        'payment_due_date' => 'date',
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
}
