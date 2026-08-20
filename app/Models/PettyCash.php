<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    protected $fillable = [
        'date',
        'description',
        'amount',
        'category',
        'person_name',
        'notes',
        'invoice_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
