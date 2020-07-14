<?php

namespace Haxibiao\Live\Traits;

use App\User;
use Haxibiao\Live\LiveRoom;
use Illuminate\Support\Facades\Redis;

trait LiveRoomResolvers
{

    /**
     * 获取直播间观众列表(在线的)
     */
    public function resolveRoomUsers($root, array $args, $context, $info)
    {
        $room = LiveRoom::find($args['room_id']);

        //获得在线的
        $json = Redis::get($room->redis_room_key);
        if (!$json) {
            return null;
        }
        $userIds = json_decode($json, true);
        // 去掉主播自己
        $online_user_ids = array_diff($userIds, array($room->user_id));
        return User::whereIn('id', $online_user_ids)->get();
    }

}
