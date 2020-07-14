<?php

namespace Haxibiao\Live\Traits;

use App\User;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveUtils;

trait LiveRepo
{

    /**
     * 获取正在推流的直播间
     */
    public static function onlineLivesQuery($pageNum, $pageSize)
    {
        //FIXME: 在线的直播间列表，应该依赖扫描结果，从db查询
        $onlineInfo     = LiveUtils::getStreamOnlineList($pageNum, $pageSize);
        $streamList     = data_get($onlineInfo, 'OnlineInfo');
        $streamNameList = [];
        foreach ($streamList as $stream) {
            $streamNameList[] = $stream['StreamName'];
        }
        return Live::whereIn('stream_name', $streamNameList);
    }

    // 更新直播间总观众数
    public function updateCountUsers(User $user)
    {
        $this->increment('count_users');
        $this->data = array_unique(array_merge($this->data ?? [], array($user->id)));
        $this->save();
    }
}
