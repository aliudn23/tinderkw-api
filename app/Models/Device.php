<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'device_type',
        'device_model',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    /**
     * Get all interactions for this device
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * Get liked people by this device
     */
    public function likedPeople()
    {
        return $this->belongsToMany(Person::class, 'interactions')
            ->wherePivot('type', 'like')
            ->withTimestamps();
    }

    /**
     * Get disliked people by this device
     */
    public function dislikedPeople()
    {
        return $this->belongsToMany(Person::class, 'interactions')
            ->wherePivot('type', 'dislike')
            ->withTimestamps();
    }
}
