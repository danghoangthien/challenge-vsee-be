<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'provider_id',
        'started_at',
        'ended_at',
        'status',
        'reason'
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
} 