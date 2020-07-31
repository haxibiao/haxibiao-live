<?php

namespace Haxibiao\Live\Traits;

use Haxibiao\Live\AppointmentLive;
use Haxibiao\Live\Notifications\UserAppointmentLive;

trait AppointmentLiveRepo
{
    /**
     * 创建直播预约
     */
    public static function createAppointmentLive($user, $live)
    {
        // 预约记录
        AppointmentLive::create([
            'user_id' => $user->id,
            'live_id' => $live->id,
        ]);

        $streamer = $live->user;
        $streamer->notify(new UserAppointmentLive($user, $live));
    }
}
