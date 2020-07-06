<?php

namespace Haxibiao\Live;

use App\User;
use App\Video;
use Haxibiao\Live\Traits\UserLiveRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 单场直播秀
 */
class UserLive extends Model
{
    use UserLiveRepo;

    protected $table = 'user_lives';

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
    public function live(): BelongsTo
    {
        return $this->belongsTo(LiveRoom::class);
    }

    /**
     * 直播回放的视频信息
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
