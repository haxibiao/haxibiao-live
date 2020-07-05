<?php

namespace Haxibiao\Live\Listeners;

use Haxibiao\Live\LiveRoom;

/**
 * 更新直播间的人数列表
 */
class UpdateUsersInRoom
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
     * @param \Haxibiao\Live\Events\UserComeIn $event
     */
    public function handle(\Haxibiao\Live\Events\UserComeIn $event)
    {
        $event->user->joinLiveRoom($event->liveRoom);
    }
}
