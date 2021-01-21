<?php

namespace Haxibiao\Live\Traits;

use App\LiveRoom;
use Haxibiao\Live\Live;

trait LiveRoomAttrs
{

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
        return cdnurl($this->cover) ?? 'https://dtzq-1251052432.cos.ap-shanghai.myqcloud.com/2020-03-25/u%3A980235-screenshot-15-20-45-1192x746.jpg';
    }
}
