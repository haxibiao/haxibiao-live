<?php

namespace Haxibiao\Live;

use Haxibiao\Live\Live;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentLive extends Model
{
    protected $guarded = [];

    public function live(): BelongsTo
    {
        return $this->belongsTo(Live::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
