<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $department_id
 * @property int $role_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, VisitorRecord> $visitorRecords
 */
class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'role_id',
    ];

    /**
     * Get the user that owns the provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the visitor records for the provider.
     */
    public function visitorRecords(): HasMany
    {
        return $this->hasMany(VisitorRecord::class);
    }
} 