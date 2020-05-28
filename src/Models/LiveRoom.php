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

    protected $casts = [
        'latest_live_time' => 'datetime',
        'data'             => 'array',
    ];

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

    public function userLive(): BelongsTo
    {
        return $this->belongsTo(UserLive::class, 'id', 'live_id');
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_ON      => '正在直播',
            self::STATUS_OFF     => '已下播',
            self::STATUS_DISABLE => '异常封禁',
        ];
    }
}
