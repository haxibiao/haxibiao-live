<?php

namespace Haxibiao\Live;

use App\User;
use Haxibiao\Live\Traits\LiveRoomAttrs;
use Haxibiao\Live\Traits\LiveRoomRepo;
use Haxibiao\Live\Traits\LiveRoomResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 直播间
 */
class LiveRoom extends Model
{

    use LiveRoomRepo;
    use LiveRoomResolvers;
    use LiveRoomAttrs;

    protected $table = 'live_rooms';

    public const protocol = 'rtmp';

    public const prefix = self::protocol . '://';

    protected $casts = [
        'data' => 'array',
    ];

    protected $guarded = [];

    //-1:下直播 -2:直播间被封 1:直播中
    public const STATUS_ON      = 1;
    public const STATUS_OFF     = -1;
    public const STATUS_DISABLE = -2;

    //主播
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 多场用户直播秀
     */
    public function userLives(): HasMany
    {
        return $this->hasMany(UserLive::class, 'id', 'live_id');
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ON      => '正在直播',
            self::STATUS_OFF     => '已下播',
            self::STATUS_DISABLE => '异常封禁',
        ];
    }
}
