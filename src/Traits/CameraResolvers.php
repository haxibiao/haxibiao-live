<?php


namespace Haxibiao\Live\Traits;


use App\Exceptions\UserException;
use App\User;
use Haxibiao\Live\Camera;
use Illuminate\Support\Facades\Redis;

trait CameraResolvers
{
    /**
     * 创建实况
     */
    public function resolveStoreCamera($root, array $args, $context, $info){
        $user = getUser();

        $title          = data_get($args,'title');
        $description    = data_get($args,'description');
        $cover          = data_get($args,'cover');

        return $user->openCamera($title,$description,$cover);
    }

    /**
     * 更新实况
     */
    public function resolveUpdateCamera($root, array $args, $context, $info){
        $camera = Camera::find(data_get($args,'camera_id'));

        $title          = data_get($args,'title');
        $description    = data_get($args,'description');
        $cover          = data_get($args,'cover');
        $visibility     = data_get($args,'visibility');

        return $camera->updateUpdateCamera($title,$description,$cover,$visibility);
    }

    /**
     * 推荐的实况列表
     */
    public function resolveRecommendCameras($root, array $args, $context, $info){
        // 用户未登录，返回所有不需要权限的实况
        return Camera::where('uids','["*"]')
            ->whereStatus(Camera::STATUS_ONLINE)
            ->where('is_push_stream',true);
    }

    /**
     * 实况在线观众
     */
    public function resolveCameraUsers($root, array $args, $context, $info){
        $camera = Camera::find($args['camera_id']);

        //获得在线的
        $json = Redis::get($camera->redis_key);
        if (!$json) {
            return null;
        }
        $userIds = json_decode($json, true);
        $online_user_ids = array_diff($userIds, array($camera->user_id));
        return User::whereIn('id', $online_user_ids);
    }

    /**
     * 加入实况
     */
    public function resolveJoinCamera($root, array $args, $context, $info){
        $user = getUser();
        $camera = Camera::find($args['camera_id']);
        if ($camera->status < 0) {
            throw new UserException('抱歉，实况已下播~');
        }
        //成功返回直播
        return $camera;
    }

    /**
     * 关闭实况
     */
    public function resolveCloseCamera($root, array $args, $context, $info){
        $user = getUser();
        $camera = Camera::find($args['camera_id']);
        // 不是创建者不能关
        if ($user->id !== $camera->user_id) {
            throw new UserException('关闭实况失败~');
        }

        $camera->status = Camera::STATUS_OFFLINE;

        // 关闭直播间需要刷新推流key
        $camera->push_stream_key = null;

        $camera->save();
        return true;
    }
}