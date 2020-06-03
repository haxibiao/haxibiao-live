<?php

namespace haxibiao\live\Listeners;

use haxibiao\live\LiveRoom;

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
     * @param \haxibiao\live\Events\NewUserComeIn $event
     */
    public function handle(\haxibiao\live\Events\NewUserComeIn $event)
    {
        LiveRoom::joinLiveRoom($event->user, $event->liveRoom);
    }
}
