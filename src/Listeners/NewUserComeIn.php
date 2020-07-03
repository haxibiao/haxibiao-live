<?php

namespace Haxibiao\Live\Listeners;

use Haxibiao\Live\LiveRoom;

class NewUserComeIn
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
     * @param \Haxibiao\Live\Events\NewUserComeIn $event
     */
    public function handle(\Haxibiao\Live\Events\NewUserComeIn $event)
    {
        LiveRoom::joinLiveRoom($event->user, $event->liveRoom);
    }
}
