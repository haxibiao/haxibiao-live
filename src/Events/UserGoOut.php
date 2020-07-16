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
 * 用户退出直播间
 */
class UserGoOut implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $live;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Live $live)
    {
        $this->user = $user;
        $this->live = $live;
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'       => $this->user->id,
            'user_name'     => $this->user->name,
            'message'       => "{$this->user->name} 离开了直播房间",
            'count_onlines' => $this->live->count_onlines,
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
        return 'user_go_out';
    }
}
