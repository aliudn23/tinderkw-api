<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaction extends Model
{
    protected $fillable = [
        'device_id',
        'person_id',
        'type',
    ];

    /**
     * Get the device that made this interaction
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the person that was interacted with
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
