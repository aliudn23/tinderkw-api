<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'name',
        'age',
        'pictures',
        'latitude',
        'longitude',
        'city',
        'country',
        'like_count',
    ];

    protected $casts = [
        'pictures' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'like_count' => 'integer',
    ];

    /**
     * Get all interactions for this person
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * Get likes for this person
     */
    public function likes(): HasMany
    {
        return $this->interactions()->where('type', 'like');
    }

    /**
     * Increment like count
     */
    public function incrementLikeCount(): void
    {
        $this->increment('like_count');
    }
}
