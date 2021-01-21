<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
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

    public function getPushUrlAttribute(): string
    {
        return LiveRoom::prefix . $this->push_stream_url . "/" . $this->push_stream_key;
    }

    public function getPullUrlAttribute(): string
    {
        $live = $this;
        return LiveRoom::prefix . config('live.live_pull_domain') . "/" . config('live.app_name') . "/"
        . $live->stream_name;
    }

    public function getCoverUrlAttribute(): string
    {
        return cdnurl($this->cover) ?? 'https://dtzq-1251052432.cos.ap-shanghai.myqcloud.com/2020-03-25/u%3A980235-screenshot-15-20-45-1192x746.jpg';
    }
}
