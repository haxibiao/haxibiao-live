<?php

namespace Haxibiao\Live\Traits;

/**
 * 直播秀的属性
 */
trait LiveAttrs
{
    /**
     * 获取单场直播的唯一流名称
     */
    public function getStreamNameAttribute()
    {
        $user    = $this->user;
        $live_id = $this->id;
        return "u{$user->id}l${live_id}";
    }
}
