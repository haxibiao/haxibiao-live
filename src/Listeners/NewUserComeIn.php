<?php

namespace Haxibiao\Live\Listeners;

use Haxibiao\Live\Models\LiveRoom;

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
     * @param \App\Events\LiveRoom\NewUserComeIn $event
     * @return void
     */
    public function handle(\App\Events\LiveRoom\NewUserComeIn $event)
    {
        LiveRoom::joinLiveRoom($event->user, $event->liveRoom);
    }
}
