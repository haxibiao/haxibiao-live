<?php

namespace Haxibiao\Live\Traits;

use App\Video;
use Haxibiao\Base\User;
use Haxibiao\Helpers\VodUtils;
use Haxibiao\Live\Jobs\ProcessLiveRecordingVodFile;
use Haxibiao\Live\LiveRoom;
use Haxibiao\Live\UserLive;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function lives(): HasMany
    {
        return $this->hasMany(UserLive::class);
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
     * 用户最近的直播秀 保存回放用
     */
    public function getCurrentLive()
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
        $room->title           = $title; //FIXME: 后续需要记录用户每次开播更改过的历史标题
        $room->save();

        // 设置redis 直播室初始值
        Redis::set($room->redis_room_key, json_encode(array($user->id)));

        return $room;
    }

    /**
     * 加入直播间
     */
    public function joinLiveRoom(LiveRoom $room)
    {
        $user = $this; // 观众
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
            // 记录到主播的直播记录中
            $streamer = $room->user;
            $live     = $streamer->getCurrentLive();
            $live->updateCountUsers($user);
        }
    }

    /**
     * 离开直播间
     */
    public function leaveLiveRoom(LiveRoom $room)
    {
        $user     = $this;
        $user_ids = Redis::get($room->redis_room_key);
        if ($user_ids) {
            $userIds = json_decode($user_ids, true);
            // 从数组中删除要离开的用户
            $userIds = array_diff($userIds, array($user->id));
            Redis::set($room->redis_room_key, json_encode($userIds));
        }
    }

    /**
     * 处理直播录制视频回调
     */
    public static function processLiveRecording($fileId, $user)
    {
        VodUtils::makeCoverAndSnapshots($fileId);
        $video = new Video([
            'qcvod_fileid' => $fileId,
            'user_id'      => $user->id,
        ]);
        // 填充重要信息
        $videoInfo       = VodUtils::getVideoInfo($video->qcvod_fileid);
        $duration        = data_get($videoInfo, 'basicInfo.duration');
        $sourceVideoUrl  = data_get($videoInfo, 'basicInfo.sourceVideoUrl');
        $video->path     = $sourceVideoUrl;
        $video->duration = $duration;
        $video->disk     = 'vod';
        $video->save();
        //触发保存截图和更新主播直播时长
        dispatch(new ProcessLiveRecordingVodFile($video->id))->delay(now()->addMinute())->onQueue('video');
        return $video;
    }
}