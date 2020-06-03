<?php
namespace haxibiao\live\Traits;

use App\User;
use haxibiao\live\LiveRoom;
use haxibiao\live\UserLive;

trait UserLiveRepo
{
    // 记录用户直播数据
    public static function recordLive(User $user, LiveRoom $live)
    {
        UserLive::create([
            'user_id' => $user->id,
            'live_id' => $live->id,
        ]);
    }

    // 更新直播间总观众数
    public function updateCountUsers(User $user)
    {
        $this->increment('count_users');
        $this->data = array_unique(array_merge($this->data, array($user->id)));
        $this->save();
    }

}
