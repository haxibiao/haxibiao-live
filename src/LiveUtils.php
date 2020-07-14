<?php

namespace Haxibiao\Live;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Live\V20180801\LiveClient;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamStateRequest;
use TencentCloud\Live\V20180801\Models\DropLiveStreamRequest;
use TencentCloud\Live\V20180801\Models\ResumeLiveStreamRequest;

/**
 * 直播相关工具类Facade
 */
class LiveUtils
{
    private static $instance = null;
    public $liveClient;

    //单例
    public static function getInstance()
    {
        if (!LiveUtils::$instance) {
            LiveUtils::$instance = new LiveUtils();
        }
        return LiveUtils::$instance;
    }

    /**
     * 可在 https://console.cloud.tencent.com/cam/capi
     * 查看密钥id与key
     */
    public function __construct()
    {
        $secretId    = config('live.secret_id');
        $secretKey   = config('live.secret_key');
        $cred        = new Credential($secretId, $secretKey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("live.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client           = new LiveClient($cred, config('live.location'), $clientProfile);
        $this->liveClient = $client;
    }

    /**
     * 获取正在直播的流名称列表
     * @param $pageNum 当前页码
     * @param $PageSize 页面大小
     */
    public static function getStreamOnlineList($pageNum = 1, $PageSize = 10): array
    {
        $req    = new DescribeLiveStreamOnlineListRequest();
        $params = sprintf('{"PageNum":%d,"PageSize":%d,"DomainName":%s}', $pageNum, $PageSize, config('live.live_push_domain'));
        $req->fromJsonString($params);
        $resp = LiveUtils::getInstance()->liveClient->DescribeLiveStreamOnlineList($req);
        return json_decode($resp->toJsonString(), true);
    }

    /**
     * 查询单个流的直播状态
     * @param $streamName 流名称
     * @return active、inactive 代表正在直播与未在直播
     */
    public static function getLiveSteamStatus(string $streamName): string
    {
        $req    = new DescribeLiveStreamStateRequest();
        $params = sprintf('{"AppName":"live","DomainName":"%s","StreamName":"%s"}', config('live.live_push_url'), $streamName);

        $req->fromJsonString($params);
        $resp   = LiveUtils::getInstance()->liveClient->DescribeLiveStreamState($req);
        $result = json_decode($resp->toJsonString(), true);
        return data_get($result, 'StreamState');
    }

    /**
     * 封禁单个直播流
     * @param $streamName 流名称
     */
    public static function dropLiveStream(string $streamName)
    {
        $req    = new DropLiveStreamRequest();
        $params = sprintf('{"AppName":"live","DomainName":"%s","StreamName":"%s"}', config('live.live_push_url'), $streamName);
        $req->fromJsonString($params);
        $resp = LiveUtils::getInstance()->liveClient->DropLiveStream($req);
        return json_decode($resp->toJsonString(), true);
    }

    /**
     * 恢复单个直播推流
     * @param $streamName 流名称
     */
    public static function resumeLiveStream(string $streamName)
    {
        $req    = new ResumeLiveStreamRequest();
        $params = sprintf('{"AppName":"live","DomainName":"%s","StreamName":"%s"}', config('live.live_push_url'), $streamName);
        $req->fromJsonString($params);

        $resp = LiveUtils::getInstance()->liveClient->ResumeLiveStream($req);

        return json_decode($resp->toJsonString(), true);
    }

    /**
     * 获取腾讯云推流密钥(主播使用)
     * @param $domain
     * @param $streamName
     * @param $key
     * @param null $endTime
     * @return string
     */
    public static function genPushKey($streamName): string
    {
        //直播结束时间
        $endTime = now()->addDay()->toDateTimeString();
        $key     = config('live.live_key');

        if ($key && $endTime) {
            $txTime   = strtoupper(base_convert(strtotime($endTime), 10, 16));
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str  = '?' . http_build_query(array(
                'txSecret' => $txSecret,
                'txTime'   => $txTime,
            ));
        }
        return $streamName . ($ext_str ?? '');
    }

    public static function getPushUrl()
    {
        return config('live.live_push_domain') . "/" . config('live.app_name');
    }

    public static function getPullUrl()
    {
        return config('live.live_pull_domain') . "/" . config('live.app_name');
    }
}
