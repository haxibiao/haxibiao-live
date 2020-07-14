<?php

namespace Haxibiao\Live\Traits;

use App\User;

trait LiveRepo
{
    // 更新直播间总观众数
    public function updateCountUsers(User $user)
    {
        $this->increment('count_users');
        $this->data = array_unique(array_merge($this->data ?? [], array($user->id)));
        $this->save();
    }
}
