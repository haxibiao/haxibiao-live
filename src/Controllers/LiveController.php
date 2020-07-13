<?php

namespace Haxibiao\Live\Controllers;

use App\Http\Controllers\Controller;
use Haxibiao\Live\LiveRoom;
use Illuminate\Http\Request;

class LiveController extends Controller
{

    /**
     * 获取在线直播间列表（web-api）
     */
    public function getOnlineLiveRoomList(Request $request)
    {
        $pageNum = $request->get('pageNum');
        return LiveRoom::take(10)->get();
        // return LiveRoom::onlineRoomsQuery($pageNum, 10)->get();
    }

    /**
     * 分享页面
     */
    public function share($id)
    {
        $room = LiveRoom::find($id);
        abort_if(empty($room), 404, '未找到页面');
        return view('live.share', [
            'pull_domain' => config('live.live_pull_domain'),
            'stream_name' => $room->stream_name,
            'title'       => $room->title,
        ]);
    }

}
