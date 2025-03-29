<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class LoungeQueue extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'waiting_room_queue';

    protected $fillable = [
        'visitor_id',
        'user_id',
        'joined_at',
        'position',
        'reason'
    ];

    protected $casts = [
        'joined_at' => 'datetime'
    ];
} 