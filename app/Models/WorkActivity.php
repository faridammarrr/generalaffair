<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkActivity extends Model
{
    protected $fillable = [
        'ticket_number',
        'date',
        'category',
        'priority',
        'title',
        'description',
        'requester',
        'assignee',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkActivity $activity) {
            if (empty($activity->ticket_number)) {
                $maxId = static::max('id') ?? 0;
                $activity->ticket_number = 'TK-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
