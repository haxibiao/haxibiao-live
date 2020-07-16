<?php

namespace Haxibiao\Live\Events;

use App\User;
use Haxibiao\Live\Live;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 某场直播的评论事件(socket到前端)
 */
class NewLiveMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $live;

    /**
     * Create a new event instance.
     *
     * @param $userId 观众id
     * @param $liveRoomId 直播室id
     * @param $message 弹幕内容
     */
    public function __construct($userId, $live_id, $message)
    {
        $this->user    = User::find($userId);
        $this->live    = Live::find($live_id);
        $this->message = $message;
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'     => $this->user->id,
            'user_name'   => $this->user->name,
            'user_avatar' => $this->user->avatar_url,
            'live_id'     => $this->live->id,
            'message'     => $this->message,
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('live_.' . $this->live->id);
    }

    public function broadcastAs(): string
    {
        return 'new_comment';
    }
}
