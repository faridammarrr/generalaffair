<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelBudget extends Model
{
    protected $table = 'fuel_budgets';

    protected $fillable = [
        'month',
        'year',
        'amount',
        'notes',
    ];
}
