<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampMovement extends Model
{
    protected $fillable = [
        'description',
        'person_name',
        'division',
        'quantity',
        'direction',
        'moved_at',
    ];

    protected $casts = [
        'moved_at' => 'date',
    ];

    public function getSignedQuantityAttribute(): int
    {
        if ($this->direction === 'in') {
            return $this->quantity;
        }

        return -$this->quantity;
    }
}
