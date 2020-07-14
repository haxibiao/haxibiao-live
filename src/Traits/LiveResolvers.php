<?php

namespace Haxibiao\Live\Traits;

use App\Exceptions\UserException;
use Haxibiao\Live\Events\NewLiveRoomMessage;
use Haxibiao\Live\Events\UserComeIn;
use Haxibiao\Live\Events\UserGoOut;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
use Illuminate\Support\Arr;

trait LiveResolvers
{
    /**
     * 推荐直播列表(就是目前在播的)
     */
    public function resolveRecommendLives($root, array $args, $context, $info)
    {
        $pageNum = data_get($args, 'page', 1);
        $page    = data_get($args, 'first', data_get($args, 'page'));
        return Live::onlineLivesQuery($pageNum, $page);
    }

    /**
     * 开直播
     */
    public function resolveOpen($root, array $args, $context, $info)
    {
        $user  = getUser();
        $title = data_get($args, 'title', null);

        throw_if(!$title, UserException::class, '请输入直播间标题~');

        // 开直播
        return $user->openLive($title);
    }

    /**
     * 加入直播间
     */
    public function resolveJoin($root, array $args, $context, $info)
    {
        $user = getUser();
        $live = Live::find($args['live_id']);
        throw_if(!is_testing_env() || !$live->status, UserException::class, '抱歉，主播已下播~');

        $liveRoom = $live->room;

        // UI事件：
        event(new UserComeIn($user, $liveRoom));

        //加入直播间
        $user->joinLiveRoom($liveRoom);

        //成功返回直播间信息
        return $liveRoom;

    }

    /**
     * 发送弹幕
     */
    public function resolveComment($root, array $args, $context, $info)
    {
        $user = getUser();
        $live = Live::find($args['live_id']);

        $message = Arr::get($args, 'message', null);
        event(new NewLiveRoomMessage($user->id, $live->room_id, $message));
        return 1;
    }

    /**
     * 用户离开直播间
     */
    public function resolveLeave($root, array $args, $context, $info)
    {
        $user = getUser();
        $live = Live::find($args['live_id']);
        $room = $live->room;

        // 发socket通知
        event(new UserGoOut($user, $room));
        $user->leaveLiveRoom($room);
        return $room;
    }

    /**
     * 主播关闭直播秀
     */
    public function resolveClose($root, array $args, $context, $info)
    {
        $live = Live::find($args['live_id']);
        $user = getUser();
        $room = $live->room;

        // 不是创建者不能关
        if ($room && $user->id !== $live->user_id) {
            throw new UserException('关闭直播秀失败~');
        }

        $live->status = Live::STATUS_OFFLINE;

        // 关闭直播间需要刷新推流key
        $live->push_stream_key = null;

        $live->save();

        // 发socket通知
        LiveRoom::closeLiveRoom($room);
        return true;
    }

    /**
     * 直播间异常（断流）
     * @deprecated
     */
    public function resolveExceptionFired($root, array $args, $context, $info)
    {
        // $live_room_id = Arr::get($args, 'live_room_id', null);
        // $room         = LiveRoom::find($live_room_id);
        // $room->increment('count_exception');
        // // 两名观众监测了异常，直接关闭
        // if ($room->count_exception >= 1 && $room->status === LiveRoom::STATUS_ON) {
        //     LiveRoom::closeLiveRoom($room);
        // }
        return true;
    }
}
