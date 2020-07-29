<?php

namespace Haxibiao\Live\Jobs;

use App\Video;
use Haxibiao\Helpers\VodUtils;
use Haxibiao\Live\Live;
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
class ProcessRecording implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $live;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Live $live)
    {
        $this->live = $live;
        $this->delay(now()->addMinute());
        $this->onQueue('video');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //回放视频
        $video = $this->live->video;

        // 获取vod视频信息
        $videoInfo = VodUtils::getVideoInfo($video->qcvod_fileid);
        //cdn
        $video->path = data_get($videoInfo, 'basicInfo.sourceVideoUrl');
        //时长
        $video->duration = data_get($videoInfo, 'basicInfo.duration');
        //封面
        $video->cover = data_get($videoInfo, 'basicInfo.coverUrl');
        $video->disk  = 'vod';
        $video->save();

        // 视频预热
        self::pushUrlCacheWithVODUrl($video->path);
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
