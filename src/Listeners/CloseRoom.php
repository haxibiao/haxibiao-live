<?php

namespace Haxibiao\Live\Listeners;

use Haxibiao\Live\LiveRoom;

class CloseRoom
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
     * @param \Haxibiao\Live\Events\CloseRoom $event
     */
    public function handle(\Haxibiao\Live\Events\CloseRoom $event)
    {
        $room = $event->liveRoom;
        // 关闭直播间需要刷新直播间状态和推流key
        $room->update([
            'push_stream_key' => null,
            'status'          => LiveRoom::STATUS_OFF,
        ]);
    }
}
