<?php

namespace Haxibiao\Live\Listeners;

use Illuminate\Support\Facades\Redis;

class UserGoOut
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

        $users = Redis::get($room->redis_room_key);
        if ($users) {
            $userIds = json_decode($users, true);
            // 从数组中删除要离开的用户
            $userIds = array_diff($userIds, array($user->id));
            Redis::set($room->redis_room_key, json_encode($userIds));
        }
    }
}
