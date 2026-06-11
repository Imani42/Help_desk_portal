<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fault extends Model
{
    protected $fillable = [
    'user_id',
    'type',
    'description',
    'location',
    'status',
    'technician_id'

    
];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FaultComment::class)->whereNull('parent_id')->latest();
    }
} 
