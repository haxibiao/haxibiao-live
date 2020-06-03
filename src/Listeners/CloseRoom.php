<?php

namespace haxibiao\live\Listeners;

use haxibiao\live\LiveRoom;

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
     * @param \haxibiao\live\Events\CloseRoom $event
     */
    public function handle(\haxibiao\live\Events\CloseRoom $event)
    {
        $room = $event->liveRoom;
        // 关闭直播间需要刷新直播间状态和推流key
        $room->update([
            'push_stream_key' => null,
            'status'          => LiveRoom::STATUS_OFF,
        ]);
    }
}
