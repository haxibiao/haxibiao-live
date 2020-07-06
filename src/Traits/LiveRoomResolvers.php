<?php

namespace Haxibiao\Live\Traits;

use App\Exceptions\UserException;
use App\User;
use Haxibiao\Live\Events\NewLiveRoomMessage;
use Haxibiao\Live\Events\UserComeIn;
use Haxibiao\Live\Events\UserGoOut;
use Haxibiao\Live\LiveRoom;
use Haxibiao\Live\LiveUtils;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

trait LiveRoomResolvers
{
    /**
     * 推荐直播间列表(就是目前在播的)
     */
    public function resolveRecommendLiveRoom($root, array $args, $context, $info)
    {
        $utils = LiveUtils::getInstance();
        // $pageSize       = data_get($args, 'page_size');
        // $pageNum        = data_get($args, 'page_num');
        $pageSize = data_get($args, 'count', data_get($args, 'first', 10)); //TODO:兼容老版本写法 以前使用的是 paginate 写法 参数名字为 first 和 page
        $pageNum  = data_get($args, 'page');
        //获取在线直播间stream_names
        $onlineInfo     = $utils->getStreamOnlineList((int) $pageNum, (int) $pageSize);
        $streamList     = data_get($onlineInfo, 'OnlineInfo');
        $streamNameList = [];
        foreach ($streamList as $stream) {
            $streamNameList[] = $stream['StreamName'];
        }
        return LiveRoom::whereIn('stream_name', $streamNameList); //TODO:使用 paginate 不需要 get
    }

    /**
     * 开直播
     */
    public function resolveOpen($root, array $args, $context, $info)
    {
        $user  = getUser();
        $title = data_get($args, 'title', null);

        throw_if(!$title, UserException::class, '请输入直播间标题~');
        throw_if(!$user->canOpenLive(), UserException::class, '您没有开启直播的权限哦~');

        // 开直播
        $room = $user->openLiveRoom($title);

        return $room;
    }

    /**
     * 加入直播间
     */
    public function resolveJoin($root, array $args, $context, $info)
    {
        $user       = getUser();
        $liveRoomId = Arr::get($args, 'live_room_id', null);
        $liveRoom   = LiveRoom::find($liveRoomId);

        //未下播
        if ($userIds = Redis::get($liveRoom->redis_room_key)) {
            $userIds = json_decode($userIds, true);
            //未加入过
            if (array_search($user->id, $userIds) === false) {
                //事件：加入直播间
                event(new UserComeIn($user, $liveRoom));
            }
            //成功返回直播间信息
            return $liveRoom;
        }

        if (!is_testing_env()) {
            throw new UserException('主播已经下播,下次早点来哦~');
        }
    }

    /**
     * 发送弹幕
     */
    public function resolveComment($root, array $args, $context, $info)
    {
        $user         = getUser();
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $message      = Arr::get($args, 'message', null);

        event(new NewLiveRoomMessage($user->id, $live_room_id, $message));
        return $message;
    }

    /**
     * 用户离开直播间
     */
    public function resolveLeave($root, array $args, $context, $info)
    {
        $user         = getUser();
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $room         = LiveRoom::find($live_room_id);

        if ($usersData = Redis::get($room->redis_room_key)) {
            $userIds = json_decode($usersData, true);

            // 保证不多给前端发通知
            if (array_search($user->id, $userIds) !== false) {
                event(new UserGoOut($user, $room));
            }
        }

        return $room;
    }

    /**
     * 主播关闭直播间
     */
    public function resolveClose($root, array $args, $context, $info)
    {
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $user         = getUser();
        $room         = LiveRoom::find($live_room_id);
        if ($room && $user->id !== $room->user_id) {
            throw new UserException('关闭直播失败~');
        }
        LiveRoom::closeLiveRoom($room);
        return true;
    }

    /**
     * 获取直播间观众列表(在线的)
     */
    public function resolveOnlineUsers($root, array $args, $context, $info)
    {
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $room         = LiveRoom::find($live_room_id);

        //获得在线的
        $users_ids_json = Redis::get($room->redis_room_key);
        if (!$users_ids_json) {
            return null;
        }
        $userIds = json_decode($users_ids_json, true);
        // 去掉主播自己
        $online_user_ids = array_diff($userIds, array($room->user_id));
        return User::whereIn('id', $online_user_ids)->get();
    }

    /**
     * 直播间异常（断流）
     */
    public function resolveExceptionFired($root, array $args, $context, $info)
    {
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $room         = LiveRoom::find($live_room_id);
        $room->increment('count_exception');
        // 两名观众监测了异常，直接关闭
        if ($room->count_exception >= 1 && $room->status === LiveRoom::STATUS_ON) {
            LiveRoom::closeLiveRoom($room);
        }
        return true;
    }
}
