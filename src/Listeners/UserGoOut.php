<?php

namespace haxibiao\live\Listeners;

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
     * @param \haxibiao\live\Events\UserGoOut $event
     */
    public function handle(\haxibiao\live\Events\UserGoOut $event)
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
