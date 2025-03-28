<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorExamination extends Model
{
    protected $fillable = [
        'visitor_id',
        'provider_id',
        'queue_entry_id',
        'started_at',
        'ended_at',
        'status'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function queueEntry(): BelongsTo
    {
        return $this->belongsTo(LoungeQueue::class, 'queue_entry_id');
    }
} 