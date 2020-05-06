<?php

namespace Haxibiao\Live\Models;

use App\User;
use Haxibiao\Live\Traits\LiveRoomAttrs;
use Haxibiao\Live\Traits\LiveRoomRepo;
use Haxibiao\Live\Traits\LiveRoomResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveRoom extends Model
{
    protected $table = 'live_rooms';

    public const protocol = 'rtmp';

    public const prefix   = self::protocol . '://';

    use LiveRoomRepo, LiveRoomResolvers, LiveRoomAttrs;

    protected $guarded = [];

    //-1:下直播 -2:直播间被封 1:直播中
    public const STATUS_ON     = 1;
    public const STATUS_OFF    = -1;
    public const STATUS_DISABLE = -2;

    public function streamer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anchor_id');
    }
}
