<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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

    /**
     * 直播间默认的直播秀
     */
    public function getLiveAttribute()
    {
        //FIXME: 断流3分钟内别关闭live status 可以连回来
        $live = $this->lives()->where('status', '>=', 0)->latest('id')->first();
        //没有，开一个，关了，再开一个
        if (is_null($live)) {
            $live = Live::create([
                'user_id'      => $this->user_id,
                'live_room_id' => $this->id,
            ]);
            return $live;
        }
        return $live;
    }

    /**
     * 直播间标题
     */
    public function getTitleAttribute()
    {
        return $this->live->title;
    }

    public function getPushUrlAttribute(): string
    {
        return LiveRoom::prefix . $this->push_stream_url . "/" . $this->push_stream_key;
    }

    public function getPullUrlAttribute(): string
    {
        $live = $this->live;
        return LiveRoom::prefix . config('live.live_pull_domain') . "/" . config('live.app_name') . "/"
        . $live->stream_name;
    }

    public function getCoverUrlAttribute(): string
    {
        return Storage::cloud()->url($this->cover) ?? 'https://dtzq-1251052432.cos.ap-shanghai.myqcloud.com/2020-03-25/u%3A980235-screenshot-15-20-45-1192x746.jpg';
    }
}
