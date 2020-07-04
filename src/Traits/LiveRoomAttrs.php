<?php

namespace Haxibiao\Live\Traits;

use App\LiveRoom;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

trait LiveRoomAttrs
{
    public function getRedisRoomKeyAttribute(): string
    {
        return "live_room_{$this->id}";
    }

    public function getCountOnlineAudienceAttribute(): int
    {
        $count = json_decode(Redis::get($this->redis_room_key), true);
        return $count ? count($count) : 0;
    }

    public function getPushUrlAttribute(): string
    {
        return LiveRoom::prefix . $this->push_stream_url . "/" . $this->push_stream_key;
    }

    public function getPullUrlAttribute(): string
    {
        return LiveRoom::prefix . config('live.live_pull_domain') . "/" . config('live.app_name') . "/" . $this->stream_name;
    }

    public function getCoverUrlAttribute(): string
    {
        return Storage::cloud()->url($this->cover) ?? 'https://dtzq-1251052432.cos.ap-shanghai.myqcloud.com/2020-03-25/u%3A980235-screenshot-15-20-45-1192x746.jpg';
    }
}
