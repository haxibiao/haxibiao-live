<?php

namespace Haxibiao\Live\Events;

use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 主播关直播间
 */
class OwnerCloseLive implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $live;
    public $message;
    /**
     * Create a new event instance.
     *
     * @param LiveRoom $liveRoom
     * @param string $message
     */
    public function __construct(Live $live, string $message)
    {
        $this->live    = $live;
        $this->message = $message;
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'live_id' => $this->live->id,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live.' . $this->live->id);
    }

    public function broadcastAs(): string
    {
        return 'close_live';
    }
}
