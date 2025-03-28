<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'provider_id',
        'reason',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
