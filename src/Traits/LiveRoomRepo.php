<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Live\Events\OwnerCloseRoom;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
use Illuminate\Support\Facades\Redis;

trait LiveRoomRepo
{

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
}
