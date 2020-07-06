<?php

namespace Haxibiao\Live\Controllers\Api;

use App\Exceptions\UserException;
use App\Http\Controllers\Controller;
use Haxibiao\Live\LiveRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LiveController extends Controller
{

    /**
     * 监听到有推流事件
     */
    public function pushStreamEvent(Request $request)
    {
        $data       = $request->all();
        $streamName = data_get($data, 'stream_id');
        $errcode    = data_get($data, 'errcode');
        if ($errcode == 0) {
            $room = LiveRoom::where('stream_name', $streamName)->first();
            // OBS开直播没有title
            if ($room && !empty($room->title)) {
                $room->update([
                    'status' => LiveRoom::STATUS_ON,
                    'title'  => '快来我的直播间🤖🤖',
                ]);
            }
        }
    }

    /**
     * 动态设置直播间封面
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

    //保存回放
    public function recording(Request $request)
    {
        $recordingInfo = $request->all();
        $vodFileId     = data_get($recordingInfo, 'file_id');
        $channelId     = data_get($recordingInfo, 'channel_id');
        $room          = LiveRoom::where('stream_name', $channelId)->first();
        if ($room) {
            $user = $room->user;
            // 保存 vod录制文件记录
            $video = \App\Video::saveByVodFileId($vodFileId, $user);
            // 在用户直播记录中 关联 直播视频文件
            $userLive                = $user->getCurrentLive();
            $userLive->video_id      = $video->id;
            $userLive->live_duration = $video->duration;
            $userLive->save();
        }
    }

    /**
     * @param Request $request
     * 腾讯云直播截图回调
     */
    public function screenShots(Request $request)
    {
        $coverInfo = $request->all();
        $channelId = Arr::get($coverInfo, 'channel_id', null);
        $room      = LiveRoom::where('stream_name', $channelId)->first();
        // 如果主播之前有自定义过封面，截图回调就不去更新直播间封面了，screenshot是腾讯云截图回调的图片的文件名称
        $isNeedUpdateCover = $room->cover ? Str::contains($room->cover, 'screenshot') : true;
        if ($channelId && $room && $isNeedUpdateCover) {
            $room->update(['cover' => $coverInfo['pic_url']]);
        }
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
            $room = LiveRoom::whereStreamName($streamName)->first();
            if ($room->status === LiveRoom::STATUS_ON) {
                LiveRoom::closeRoom($room);
            }
        }
    }
}
