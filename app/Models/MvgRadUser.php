<?php

namespace AppFree\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MvgRadUser extends Model
{
    //    /**
    //     * The attributes that are mass assignable.
    //     *
    //     * @var array<int, string>
    //     */
    //    protected $fillable = [
    //        'session_token',
    //    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
