<?php

namespace Haxibiao\Live\Traits;

use Illuminate\Support\Facades\Redis;

/**
 * 直播秀的属性
 */
trait LiveAttrs
{
    /**
     * 获取单场直播的唯一流名称
     */
    public function getStreamNameAttribute()
    {
        $user    = $this->user;
        $live_id = $this->id;
        return "u{$user->id}l${live_id}";
    }

    public function getRedisKeyAttribute(): string
    {
        return env('APP_NAME') . "_live_{$this->id}";
    }

    /**
     * 直播当前在线人数 count_onlines
     */
    public function getCountOnlinesAttribute(): int
    {
        $count = json_decode(Redis::get($this->redis_key), true);
        return $count ? count($count) : 0;
    }
}
