<?php

namespace Haxibiao\Live;

use Haxibiao\Live\Live;
use Haxibiao\Live\Traits\AppointmentLiveRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentLive extends Model
{

    use AppointmentLiveRepo;

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
