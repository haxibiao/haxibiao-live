<?php

namespace Haxibiao\Live;

use App\User;
use App\Video;
use Haxibiao\Base\Model;
use Haxibiao\Live\Traits\LiveAttrs;
use Haxibiao\Live\Traits\LiveRepo;
use Haxibiao\Live\Traits\LiveResolvers;
use Haxibiao\Live\Traits\LiveRoomResolvers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 单场直播秀
 */
class Live extends Model
{
    use LiveRepo;
    use LiveAttrs;
    use LiveResolvers;
    use LiveRoomResolvers;

    protected $table = 'lives';

    const STATUS_OFFLINE = -1;
    const STATUS_ONLINE  = 0;

    protected $casts = [
        'data' => 'array',
    ];

    protected $guarded = [];

    /**
     * 主播
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 属于哪个直播间
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(LiveRoom::class,'live_room_id');
    }

    /**
     * 直播回放的视频信息
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
