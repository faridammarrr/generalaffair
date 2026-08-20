<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fuel_usage extends Model
{
    protected $fillable = [
        'driver_name',
        'person',
        'date',
        'amount',
        'budget_month',
        'budget_year',
        'description',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
