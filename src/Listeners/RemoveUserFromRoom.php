<?php

namespace Haxibiao\Live\Listeners;

use Illuminate\Support\Facades\Redis;

/**
 * 当用户退出直播间，移除观众
 */
class RemoveUserFromRoom
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \Haxibiao\Live\Events\UserGoOut $event
     */
    public function handle(\Haxibiao\Live\Events\UserGoOut $event)
    {
        $room = $event->liveRoom;
        $user = $event->user;

        $user_ids = Redis::get($room->redis_room_key);
        if ($user_ids) {
            $userIds = json_decode($user_ids, true);
            // 从数组中删除要离开的用户
            $userIds = array_diff($userIds, array($user->id));
            Redis::set($room->redis_room_key, json_encode($userIds));
        }
    }
}
