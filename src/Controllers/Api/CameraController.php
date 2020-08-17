<?php

namespace Haxibiao\Live\Controllers\Api;

use App\Exceptions\UserException;
use App\Http\Controllers\Controller;
use App\Video;
use Haxibiao\Helpers\VodUtils;
use Haxibiao\Live\Camera;
use Haxibiao\Live\Jobs\ProcessRecording;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 慢直播相关回调类
 */
class CameraController extends Controller
{
    /**
     * 监听到有推流事件
     */
    public function pushStreamEvent(Request $request)
    {
        $data       = $request->all();
        $streamName = data_get($data, 'stream_id');
        $camera = Camera::where('stream_name', $streamName)
            ->first();
        if ($camera && !$camera->is_push_stream) {
            $camera->update([
                'is_push_stream' => true,
            ]);
        }
    }

    /**
     * @param Request $request
     * 腾讯云慢直播断流回调
     * 错误码说明:
     * 1    recv rtmp deleteStream    主播端主动断流
     * 2    recv rtmp closeStream    主播端主动断流
     * 3    recv() return 0    主播端主动断开 TCP 连接
     */
    public function cutOutLive(Request $request)
    {
        $data = $request->all();
        $streamName = Arr::get($data, 'stream_id', null);
        $camera = Camera::where('stream_name', $streamName)
            ->first();
        if ($camera && $camera->is_push_stream) {
            $camera->update([
                'is_push_stream' => false,
            ]);
        }
    }
}
