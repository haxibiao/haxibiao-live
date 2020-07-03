<?php

namespace Haxibiao\Live\Traits;

use App\Exceptions\UserException;
use App\User;
use Haxibiao\Live\Events\NewLiveRoomMessage;
use Haxibiao\Live\Events\NewUserComeIn;
use Haxibiao\Live\Events\UserGoOut;
use Haxibiao\Live\LiveRoom;
use haxibiao\user\HXBUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Throwable;

trait LiveRoomResolvers
{
    /**
     * 推荐直播间列表
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return LiveRoom|Builder
     */
    public function recommendLiveRoom($root, array $args, $context, $info)
    {
        $live_utils     = app('live_utils');
        $pageSize       = data_get($args, 'page_size');
        $pageNum        = data_get($args, 'page_num');
        $onlineInfo     = $live_utils->getStreamOnlineList($pageNum, $pageSize);
        $streamList     = data_get($onlineInfo, 'OnlineInfo');
        $streamNameList = [];
        foreach ($streamList as $stream) {
            $streamNameList[] = $stream['StreamName'];
        }
        return self::whereIn('stream_name', $streamNameList)->get();
    }

    /**
     * 创建直播室
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return LiveRoom|mixed
     * @throws UserException
     * @throws Throwable
     */
    public function createLiveRoomResolver($root, array $args, $context, $info)
    {
        $user  = getUser();
        $title = data_get($args, 'title', null);
        $this->checkUser($user);

        throw_if(!$title, UserException::class, '请输入直播间标题~');

        // 开过直播室,更新直播间信息即可
        if ($liveRoom = $user->liveRoom) {
            $liveRoom = self::openLive($user, $liveRoom, $title);
        } else {
            // 创建直播室
            $liveRoom = self::createLiveRoom($user, $title);
        }
        return $liveRoom;
    }

    /**
     * 加入直播间
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return LiveRoom|LiveRoom[]|Collection|Model|mixed|null
     * @throws UserException
     */
    public function joinLiveRoomResolver($root, array $args, $context, $info)
    {
        $user       = getUser();
        $liveRoomId = Arr::get($args, 'live_room_id', null);
        $liveRoom   = LiveRoom::find($liveRoomId);

        if ($userIds = Redis::get($liveRoom->redis_room_key)) {
            $userIds = json_decode($userIds, true);
            if (array_search($user->id, $userIds) === false) {
                event(new NewUserComeIn($user, $liveRoom));
            }
            return $liveRoom;
        }

        if (!is_testing_env()) {
            throw new UserException('主播已经下播,下次早点来哦~');
        }

    }

    /**
     * 发送弹幕
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return mixed
     */
    public function commentLiveRoomResolver($root, array $args, $context, $info)
    {
        $user         = getUser();
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $message      = Arr::get($args, 'message', null);

        event(new NewLiveRoomMessage($user->id, $live_room_id, $message));
        return $message;
    }

    /**
     * 用户离开直播间
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return LiveRoom|LiveRoom[]|Collection|Model|mixed|null
     */
    public function leaveLiveRoomResolver($root, array $args, $context, $info)
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
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return bool
     * @throws UserException
     */
    public function closeLiveRoomResolver($root, array $args, $context, $info)
    {
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $user         = getUser();
        $room         = LiveRoom::find($live_room_id);
        if ($user->id !== $room->streamer->id) {
            throw new UserException('关闭直播失败~');
        }
        self::closeRoom($room);
        return true;
    }

    /**
     * 主播获取当前直播间观众列表
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return User[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|null
     */
    public function getLiveRoomUsers($root, array $args, $context, $info)
    {
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $room         = LiveRoom::find($live_room_id);
        $users_key    = Redis::get($room->redis_room_key);
        if (!$users_key) {
            return null;
        }

        $userIds = json_decode($users_key, true);
        // 去掉主播自己
        $userIds = array_diff($userIds, array($room->streamer->id));
        return User::whereIn('id', $userIds)->get();
    }

    /**
     * 直播间异常（断流）
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * @return bool
     */
    public function exceptionLiveRoomResolver($root, array $args, $context, $info)
    {
        $live_room_id = Arr::get($args, 'live_room_id', null);
        $room         = LiveRoom::find($live_room_id);
        $room->increment('count_exception');
        // 两名观众监测了异常，直接关闭
        if ($room->count_exception >= 1 && $room->status === LiveRoom::STATUS_ON) {
            self::closeRoom($room);
        }
        return true;
    }

    /**
     * 检测用户是否有有资格开启直播
     * @param User $user
     * @throws UserException
     */
    public function checkUser(User $user)
    {
        if (in_array($user->status, [HXBUser::MUTE_STATUS, HXBUser::DISABLE_STATUS], true)) {
            throw new UserException('您没有开启直播的权限哦~');
        }
    }
}
