<?php

namespace AppFree\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class WatchdogLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_id',
        'nanoseconds_created_at',
        'seconds_received_at',
        'seconds_to_processing'
    ];

    protected function secondsReceivedAt(): Attribute
    {
        return Attribute::make(
            set: fn (int $value) => [
                'seconds_to_processing' => $value ? ($value - $this->nanoseconds_created_at) / pow(10, 9) : null
            ],
        );
    }
}
