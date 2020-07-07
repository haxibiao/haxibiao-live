<?php

namespace Haxibaio\Live\Jobs;

use App\Video;
use Haxibiao\Helpers\VodUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\PushUrlCacheRequest;
use TencentCloud\Vod\V20180717\VodClient;

/**
 * 处理直播录制的VOD文件,更新主播直播时长和视频预热
 */
class ProcessLiveRecordingVodFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $video_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($video_id)
    {
        $this->video_id = $video_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $video    = Video::find($this->video_id);
        $fileInfo = VodUtils::getVideoInfo($video->qcvod_fileid);
        $coverUrl = data_get($fileInfo, 'basicInfo.coverUrl');
        $video->update([
            'cover' => $coverUrl,
        ]);
        // 更新用户直播记录的直播时长
        $this->updateUserLiveDuration($video);
        // 视频预热
        self::pushUrlCacheWithVODUrl($video->path);
    }

    /**
     * 更新用户直播记录的直播时长
     */
    public function updateUserLiveDuration(Video $video)
    {
        $user = $video->user;
        // $live 获得的用户最近一次的直播，假如用户1分钟内开了多次直播，可能会更新错时长
        if ($user && $live = $user->getCurrentLive()) {
            $live->update([
                'live_duration' => $video->duration,
            ]);
        }
    }

    /**
     * VOD视频资源预热
     */
    public static function pushUrlCacheWithVODUrl($url)
    {
        //VOD预热
        $cred        = new Credential(config('vod.secret_id'), config('vod.secret_key'));
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("vod.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);

        $client = new VodClient($cred, "ap-guangzhou", $clientProfile);
        $req    = new PushUrlCacheRequest();
        $params = '{"Urls":["' . $url . '"]}';

        $req->fromJsonString($params);
        $resp = $client->PushUrlCache($req);

        return $resp->toJsonString();
    }
}
