<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Breeze\User;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveAction;
use Haxibiao\Live\LiveRoom;
use Haxibiao\Live\LiveUtils;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Redis;

/**
 * 关联直播能力到User
 */
trait PlayWithLive
{
    public function myLiveRoom(): HasOne
    {
        return $this->hasOne(LiveRoom::class);
    }

    /**
     * 用户有多场直播
     */
    public function lives(): HasMany
    {
        return $this->hasMany(Live::class);
    }

    /**
     * 用户有一个直播间（默认）
     */
    public function getLiveRoomAttribute()
    {
        if ($liveRoom = $this->myLiveRoom) {
            return $liveRoom;
        }
        return LiveRoom::firstOrCreate(['user_id' => $this->id]);
    }

    /**
     * 用户默认的直播秀
     */
    public function getLiveAttribute()
    {
        return $this->lives()->latest('id')->first();
    }

    /**
     * 检测用户是否有有资格开启直播
     */
    public function canOpenLive()
    {
        $user = $this;
        return !in_array($user->status, [User::MUTE_STATUS, User::DISABLE_STATUS]);
    }

    /**
     * 开直播
     * @param string $title 直播间标题
     */
    public function openLive(string $title, $begenTime = null): Live
    {
        $user = $this; //主播
        $room = $user->liveRoom; //直播间

        //开直播
        $live       = $room->live;
        $streamName = $live->stream_name;

        //新的直播
        if (is_null($live->push_stream_key)) {
            $live->push_stream_key = LiveUtils::genPushKey($streamName, $begenTime);
            $live->push_stream_url = LiveUtils::getPushUrl();
            $live->pull_stream_url = LiveUtils::getPullUrl() . "/" . $streamName;
            $live->stream_name     = $streamName;
            if (!empty($begenTime)) {
                $live->begen_time = $begenTime;
            }
        }

        //重连回来可修改标题...
        $live->title = $title;
        $live->save();

        // 设置redis 直播室初始值
        Redis::set($live->redis_key, json_encode(array($user->id)));

        return $live;
    }

    /**
     * 加入直播间
     */
    public function joinLive(Live $live)
    {
        $room = $live->room;
        $user = $this; // 观众

        if ($json = Redis::exists($live->redis_key)) {

            if (empty($json)) {
                $appendValue = array($user->id);
            } else {
                $users = json_decode($json, true);
                //处理redis_key里数据异常，存的不是数组的情况
                $users = is_array($users) ? $users : [];
                // 将新观众记录到 value 中
                $users[]     = $user->id;
                $appendValue = $users;
            }
            // 去重
            $appendValue = array_unique($appendValue);
            Redis::set($live->redis_key, json_encode($appendValue));
            // 记录到主播的直播记录中
            $streamer = $room->user;
            $live     = $streamer->live;
            //更新总观看人数
            $live->updateCountUsers($user);
        }
    }

    /**
     * 离开直播间
     */
    public function leaveLive(Live $live)
    {
        $user = $this; //观众
        $json = Redis::get($live->redis_key);
        if ($json) {
            $userIds = json_decode($json, true);
            // 从数组中删除要离开的用户
            $userIds = array_diff($userIds, array($user->id));
            Redis::set($live->redis_key, json_encode($userIds));
            // 记录用户离开事件，同时记录观看直播时长
            LiveAction::leaveLive($live, $user);
        }
    }
}
