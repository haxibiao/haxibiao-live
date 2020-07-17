<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Live\LiveAction;
use Haxibiao\Live\LiveUser;

trait LiveActionRepo
{
    /**
     * 加入直播间
     */
    public static function joinLive($live, $user)
    {
        $joinAt = now()->diffInSeconds($live->created_at);
        $action = LiveAction::create([
            'user_id'         => $user->id,
            'live_id'         => $live->id,
            'actionable_type' => 'joins',
            'action_at'       => $joinAt,
        ]);
        $liveUser = LiveUser::firstOrCreate([
            'user_id' => $user->id,
            'live_id' => $live->id,
        ]);
        // 用户进入次数+1
        $liveUser->increment('count_joins');
        return $action;
    }

    /**
     * 离开直播间，同时会记录用户观看直播时长和离开次数
     */
    public static function leaveLive($live, $user, $leaveAt = null)
    {
        if (!$leaveAt) {
            $leaveAt = now()->diffInSeconds($live->created_at);
        }
        $leaveAction = LiveAction::create([
            'user_id'         => $user->id,
            'live_id'         => $live->id,
            'actionable_type' => 'leaves',
            'action_at'       => $leaveAt,
        ]);
        $lastJoinAt = LiveAction::where([
            'user_id'         => $user->id,
            'live_id'         => $live->id,
            'actionable_type' => 'joins',
        ])->latest('id')->select('action_at')->first()->action_at;

        // 用户实际观看直播的时间
        $duration = $leaveAt - $lastJoinAt;
        // 用户在直播秀中的数据
        $liveUser = LiveUser::where([
            'user_id' => $user->id,
            'live_id' => $live->id,
        ])->first();
        // 用户离开次数+1
        ++$liveUser->count_leaves;
        // 合并观看时长
        $liveUser->duration += $duration;
        $liveUser->save();
        return $leaveAction;
    }

    /**
     * 直播间评论
     */
    public static function commentLive($comment, $live)
    {
        $commentAt     = now()->diffInSeconds($live->created_at);
        $commentAction = LiveAction::create([
            'user_id'         => $comment->user_id,
            'live_id'         => $live->id,
            'actionable_type' => 'comments',
            'actionable_id'   => $comment->id,
            'action_at'       => $commentAt,
        ]);
        // 用户在直播秀中的数据
        $liveUser = LiveUser::where([
            'user_id' => $comment->user_id,
            'live_id' => $live->id,
        ])->first();
        $liveUser->increment('count_comments');
        return $commentAction;
    }
}
