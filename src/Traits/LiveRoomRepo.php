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

        if (Redis::exists($room->id)) {
            Redis::del($room->id);
        }

        $room->save(); //更新时间
    }
}
