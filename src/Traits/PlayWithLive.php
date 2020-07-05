<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Base\User;
use Haxibiao\Live\LiveRoom;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Redis;

/**
 * 关联直播能力到User
 */
trait PlayWithLive
{
    public function liveRoom(): HasOne
    {
        return $this->hasOne(LiveRoom::class);
    }

    /**
     * 一个用户 一个直播间（默认）
     */
    public function getLiveAttribute()
    {
        if ($live = $this->liveRoom) {
            return $live;
        }
        return LiveRoom::firstOrCreate(['user_id' => $this->id]);
    }

    /**
     * 检测用户是否有有资格开启直播
     * @param User $user
     * @throws UserException
     */
    public function canOpenLive()
    {
        $user = $this;
        return in_array($user->status, [User::MUTE_STATUS, User::DISABLE_STATUS], true);
    }

    /**
     * 开直播
     * @param string $title 直播间标题
     * @return LiveRoom 直播室对象
     */
    public function openLiveRoom(string $title): LiveRoom
    {
        $user = $this; //主播
        $room = $user->live; //房间

        $streamName = LiveRoom::makeStreamName($user);
        $key        = LiveRoom::genPushKey($streamName);

        $room->push_stream_key = $key;
        $room->push_stream_url = LiveRoom::getPushUrl();
        $room->pull_stream_url = LiveRoom::getPullUrl() . "/" . $streamName;
        $room->stream_name     = $streamName;
        $room->status          = LiveRoom::STATUS_ON;
        $room->title           = $title; //TODO: 后续需要记录用户每次开播更改过的历史标题
        $room->save();

        // 设置redis 直播室初始值
        Redis::set($room->redis_room_key, json_encode(array($user->id)));
        // 一天后过期
        Redis::expire($room->redis_room_key, now()->addDay()->diffInSeconds(now()));

        return $room;
    }

    /**
     * 加入直播间
     * @param User $user
     * @param LiveRoom $room
     * @return null
     */
    public function joinLiveRoom(LiveRoom $room)
    {
        $user = $this; //主播
        if (Redis::exists($room->redis_room_key)) {
            if (empty(Redis::get($room->redis_room_key))) {
                $appendValue = array($user->id);
            } else {
                $users       = json_decode(Redis::get($room->redis_room_key), true);
                $users[]     = $user->id;
                $appendValue = $users;
            }
            // 去重
            $appendValue = array_unique($appendValue);
            Redis::set($room->redis_room_key, json_encode($appendValue));
        }
        return null;
    }
}
