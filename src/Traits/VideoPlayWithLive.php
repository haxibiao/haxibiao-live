<?php

namespace Haxibiao\Live\Traits;

use App\Video;
use Haxibaio\Live\Jobs\ProcessLiveRecordingVodFile;
use Haxibiao\Helpers\VodUtils;

/**
 * 关联直播能力到Video
 */
trait VideoPlayWithLive
{

    /**
     * 处理直播录制视频回调
     */
    public static function processLiveRecording($fileId, $user)
    {
        VodUtils::makeCoverAndSnapshots($fileId);
        $video = new Video([
            'qcvod_fileid' => $fileId,
            'user_id'      => $user->id,
        ]);
        // 填充重要信息
        $videoInfo       = VodUtils::getVideoInfo($video->qcvod_fileid);
        $duration        = data_get($videoInfo, 'basicInfo.duration');
        $sourceVideoUrl  = data_get($videoInfo, 'basicInfo.sourceVideoUrl');
        $video->path     = $sourceVideoUrl;
        $video->duration = $duration;
        $video->disk     = 'vod';
        $video->hash     = hash_file('md5', $sourceVideoUrl);
        $video->save();
        VodUtils::simpleProcessFile($video->qcvod_fileid);
        //触发保存截图和更新主播直播时长
        dispatch(new ProcessLiveRecordingVodFile($video->id))->delay(now()->addMinute())->onQueue('video');
    }

}
