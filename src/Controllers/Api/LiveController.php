<?php

namespace Haxibiao\Live\Controllers\Api;

use App\Exceptions\UserException;
use App\Http\Controllers\Controller;
use App\Video;
use Haxibiao\Helpers\VodUtils;
use Haxibiao\Live\Jobs\ProcessRecording;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LiveController extends Controller
{

    /**
     * 分享页面
     */
    public function share($id)
    {
        $room = LiveRoom::find($id);
        abort_if(empty($room), 404, '未找到页面');
        return view('live.share', [
            'pull_domain' => config('live.live_pull_domain'),
            'stream_name' => $room->live->stream_name,
        ]);
    }

    /**
     * 监听到有推流事件
     */
    public function pushStreamEvent(Request $request)
    {
        $data       = $request->all();
        $streamName = data_get($data, 'stream_id');
        $errcode    = data_get($data, 'errcode');
        if (0 == $errcode) {
            $live = Live::where('stream_name', $streamName)->first();
            // $room = $live->room;
            // OBS开直播没有title
            if ($live && !empty($live->title)) {
                $live->update([
                    'status' => Live::STATUS_ONLINE,
                    'title'  => '快来我的直播间🤖🤖',
                ]);
            }
        }
    }

    /**
     * 用户主动设置直播间封面?
     * //FIXME: 没见到调用场景
     */
    public function setRoomCover(Request $request)
    {
        $cover  = $request->file('cover');
        $roomId = $request->input('room_id');
        if ($extension = $cover->getExtension() && $roomId) {
            // 用户上传的直播间封面与腾讯云自动截图的文件命名有明显差异
            $coverPathTmp = '/storage/app/live/%s';
            $fileName     = uniqid() . $extension;
            $coverPath    = sprintf($coverPathTmp, $fileName);
            $result       = Storage::put($coverPath, file_get_contents($cover->getRealPath()));
            if ($result) {
                $room = LiveRoom::findOrFail($roomId);
                $room->update(['cover' => $coverPath]);
            } else {
                throw new UserException('封面图片上传失败');
            }
        } else {
            throw new UserException('设置封面失败,请稍后再试~');
        }
    }

    //保存直播回放
    public function recording(Request $request)
    {
        $recordingInfo = $request->all();
        $fileId        = data_get($recordingInfo, 'file_id');
        $stream_name   = data_get($recordingInfo, 'channel_id');
        $live          = Live::where('stream_name', $stream_name)->first();
        if ($live) {

            //为vod创建video
            $video = Video::firstOrNew([
                'qcvod_fileid' => $fileId,
                'user_id'      => $live->user_id,
            ]);
            $video->save();
            // 关联回放视频
            $live->video_id = $video->id;
            $live->save();
            // 开始VOD处理
            VodUtils::makeCoverAndSnapshots($fileId);
            //触发保存截图和更新主播直播时长
            dispatch(new ProcessRecording($live));
        }
    }

    /**
     * @param Request $request
     * 腾讯云直播截图回调
     */
    public function screenShots(Request $request)
    {
        $coverInfo = $request->all();
        $channelId = data_get($coverInfo, 'channel_id', null);
        $live      = Live::where('stream_name', $channelId)->first();
        $room      = $live->room;
        // 如果主播之前有自定义过封面，截图回调就不去更新直播间封面了
        // screenshot是腾讯云截图回调的图片的文件名称
        $isNeedUpdateCover = $room->cover ? Str::contains($room->cover, 'screenshot') : true;
        $cover_cdn_url     = $coverInfo['pic_url'];
        if ($room && $isNeedUpdateCover) {
            $room->update(['cover' => $cover_cdn_url]);
        }
        //更新直播的截图
        $live->update(['cover' => $cover_cdn_url]);

    }

    /**
     * @param Request $request
     * 腾讯云直播断流回调
     * 错误码说明:
     * 1    recv rtmp deleteStream    主播端主动断流
     * 2    recv rtmp closeStream    主播端主动断流
     * 3    recv() return 0    主播端主动断开 TCP 连接
     */
    public function cutOutLive(Request $request)
    {
        $cutOutInfo = $request->all();
        $streamName = Arr::get($cutOutInfo, 'stream_id', null);
        $eventType  = Arr::get($cutOutInfo, 'event_type', null);

        if ($eventType && $streamName) {
            $live = Live::whereStreamName($streamName)->first();
            if (Live::STATUS_ONLINE === $live->status) {
                LiveRoom::closeLive($live);
            }
        }
    }
}
