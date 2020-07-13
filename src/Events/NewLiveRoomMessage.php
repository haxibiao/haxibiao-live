<?php

namespace Haxibiao\Live\Events;

use App\Comment;
use App\User;
use Haxibiao\Live\LiveRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * 直播间评论的事件(socket到前端，有特效...)
 */
class NewLiveRoomMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $liveRoom;

    /**
     * Create a new event instance.
     *
     * @param $userId 观众id
     * @param $liveRoomId 直播室id
     * @param $message 弹幕内容
     */
    public function __construct($userId, $liveRoomId, $message)
    {
        $this->user     = User::find($userId);
        $this->liveRoom = LiveRoom::find($liveRoomId);
        $this->message  = $message;
    }

    public function broadcastWith(): array
    {
        $popup = false;
        // 给大哥大姐们的彩蛋
        if (Str::contains($this->message, ['杨柳', '李峥', '胡蹦', '小谷', '罗静', '小芳', '张总', '老王', '王彬'])) {
            $popup = true;
        }
        /**
         * TODO:
         * 1. 还不能确定每一个项目的comments表结构都是如此
         * 2. 待直播日活见长,将此create事件安排到 listener 中异步执行
         */

        //FIXME:只有变现大学的表结构就是 body 其他项目是 content
        $body = 'content';
        if (config('app.name') == 'bianxiandaxue') {
            $body = 'body';
        }
        Comment::create([
            'user_id'          => $this->user->id,
            'commentable_id'   => $this->liveRoom->id,
            'commentable_type' => 'live_rooms',
            $body              => $this->message,
        ]);
        return [
            'user_id'      => $this->user->id,
            'user_name'    => $this->user->name,
            'user_avatar'  => $this->user->avatar_url,
            'live_room_id' => $this->liveRoom->id,
            'message'      => $this->message,
            // 彩蛋
            'egg'          => [
                'popup' => $popup,
                'type'  => 'BboBbo',
            ],
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('live_room.' . $this->liveRoom->id);
    }

    public function broadcastAs(): string
    {
        return 'new_comment';
    }
}
