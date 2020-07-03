<?php

namespace haxibiao\live\Traits;

use haxibiao\live\LiveRoom;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 关联直播能力的
 */
trait PlayWithLive
{
    /**
     * 一个用户一个直播间
     */
    public function liveRoom(): HasOne
    {
        return $this->hasOne(LiveRoom::class, 'anchor_id');
    }
}
