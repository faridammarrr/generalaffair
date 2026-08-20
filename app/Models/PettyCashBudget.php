<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCashBudget extends Model
{
    protected $fillable = [
        'month',
        'year',
        'amount',
        'notes',
    ];
}
