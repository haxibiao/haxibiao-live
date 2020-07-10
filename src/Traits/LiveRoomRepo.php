<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Live\Events\OwnerCloseRoom;
use Haxibiao\Live\LiveRoom;
use Haxibiao\Live\LiveUtils;
use Illuminate\Support\Facades\Redis;

trait LiveRoomRepo
{

    /**
     * 获取正在推流的直播间
     */
    public static function onlineRoomsQuery($pageNum, $pageSize)
    {
        $live_utils     = LiveUtils::getInstance();
        $onlineInfo     = $live_utils->getStreamOnlineList($pageNum, $pageSize);
        $streamList     = data_get($onlineInfo, 'OnlineInfo');
        $streamNameList = [];
        foreach ($streamList as $stream) {
            $streamNameList[] = $stream['StreamName'];
        }
        return LiveRoom::whereIn('stream_name', $streamNameList);
    }

    /**
     * 关闭直播间
     * @param LiveRoom $room
     */
    public static function closeLiveRoom(LiveRoom $room)
    {
        event(new OwnerCloseRoom($room, '主播关闭了直播~'));

        if (Redis::exists($room->redis_room_key)) {
            Redis::del($room->redis_room_key);
        }

        // 关闭直播间需要刷新推流key
        $room->update([
            'push_stream_key' => null,
        ]);
        $room->status = LiveRoom::STATUS_OFF; //直播间状态
        $room->save(); //更新时间

    }

    /**
     * 获取腾讯云推流密钥(主播使用)
     * @param $domain
     * @param $streamName
     * @param $key
     * @param null $endTime
     * @return string
     */
    public static function genPushKey($streamName): string
    {
        //直播结束时间
        $endTime = now()->addDay()->toDateTimeString();
        $key     = config('live.live_key');

        if ($key && $endTime) {
            $txTime   = strtoupper(base_convert(strtotime($endTime), 10, 16));
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str  = '?' . http_build_query(array(
                'txSecret' => $txSecret,
                'txTime'   => $txTime,
            ));
        }
        return $streamName . ($ext_str ?? '');
    }

    /**
     * 获取流名称（唯一）
     * @param User $user
     * @return string
     */
    public static function makeStreamName($user)
    {
        return "u{$user->id}";
    }

    public static function getPushUrl()
    {
        return config('live.live_push_domain') . "/" . config('live.app_name');
    }

    public static function getPullUrl()
    {
        return config('live.live_pull_domain') . "/" . config('live.app_name');
    }
}
