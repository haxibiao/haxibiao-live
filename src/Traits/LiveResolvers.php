<?php

namespace Haxibiao\Live\Traits;

use App\Comment;
use App\Exceptions\UserException;
use Haxibiao\Live\Events\NewLiveMessage;
use Haxibiao\Live\Events\UserComeIn;
use Haxibiao\Live\Events\UserGoOut;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveAction;
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
        if ($live->status < 0) {
            throw new UserException('抱歉，主播已下播~');
        }

        // UI事件：
        event(new UserComeIn($user, $live));

        //加入直播间
        $user->joinLive($live);

        //成功返回直播
        return $live;

    }

    /**
     * 发送弹幕
     */
    public function resolveComment($root, array $args, $context, $info)
    {
        $user = getUser();
        $live = Live::find($args['live_id']);

        $message = Arr::get($args, 'message', null);
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
        $comment = Comment::create([
            'user_id'          => $user->id,
            'commentable_id'   => $live->id,
            'commentable_type' => 'lives',
            $body              => $message,
        ]);
        // 记录用户弹幕评论
        LiveAction::commentLive($comment, $live);
        event(new NewLiveMessage($user->id, $live->id, $message));
        return 1;
    }

    /**
     * 用户离开直播间
     */
    public function resolveLeave($root, array $args, $context, $info)
    {
        $user = getUser();
        $live = Live::find($args['live_id']);

        // 发socket通知
        event(new UserGoOut($user, $live));
        $user->leaveLive($live);
        return $live;
    }

    /**
     * 主播关闭直播秀
     */
    public function resolveClose($root, array $args, $context, $info)
    {
        $live = Live::find($args['live_id']);
        $user = getUser();

        // 不是创建者不能关
        if ($user->id !== $live->user_id) {
            throw new UserException('关闭直播秀失败~');
        }

        $live->status = Live::STATUS_OFFLINE;

        // 关闭直播间需要刷新推流key
        $live->push_stream_key = null;

        $live->save();

        // 发socket通知
        Live::closeLive($live);
        return true;
    }

}
