<?php

namespace Haxibiao\Live\Events;

use App\User;
use Haxibiao\Live\LiveRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 用户进入直播间
 */
class UserComeIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $liveRoom;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param LiveRoom $liveRoom
     */
    public function __construct(User $user, LiveRoom $liveRoom)
    {
        $this->user     = $user;
        $this->liveRoom = $liveRoom;
    }

    public function broadcastWith(): array
    {

        return [
            'user_id'        => $this->user->id,
            'user_name'      => $this->user->name,
            'user_avatar'    => $this->user->avatar_url,
            'message'        => "{$this->user->name} 进入了直播房间",
            'count_audience' => $this->liveRoom->count_online_audience,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('live_room.' . $this->liveRoom->id);
    }

    public function broadcastAs(): string
    {
        return 'user_come_in';
    }
}
