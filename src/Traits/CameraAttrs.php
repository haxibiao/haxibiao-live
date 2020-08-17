<?php


namespace Haxibiao\Live\Traits;


use Haxibiao\Live\Camera;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

trait CameraAttrs
{
    /**
     * 获取实况的唯一流名称
     */
    public function getStreamNameAttribute()
    {
        $user      = $this->user;
        $camera_id = $this->id;
        return "u{$user->id}c{$camera_id}";
    }

    /**
     * 获取RedisKey
     */
    public function getRedisKeyAttribute(): string
    {
        return config('app.name') . "_camera_{$this->id}";
    }

    /**
     * 在线人数
     */
    public function getCountOnlinesAttribute(): int
    {
        $count = json_decode(Redis::get($this->redis_key), true);
        return $count ? count($count) : 0;
    }

    /**
     * 推流地址
     */
    public function getPushUrlAttribute(): string
    {
        return Camera::prefix . $this->push_stream_url . "/" . $this->push_stream_key;
    }

    /**
     * 拉流地址
     */
    public function getPullUrlAttribute(): string
    {
        $live = $this;
        return Camera::prefix . config('camera.camera_pull_domain') . "/" . config('camera.app_name') . "/"
            . $live->stream_name;
    }

    /**
     * 封面图
     * @return string
     */
    public function getCoverUrlAttribute(): string
    {
        return $this->cover?? 'https://dtzq-1251052432.cos.ap-shanghai.myqcloud.com/2020-03-25/u%3A980235-screenshot-15-20-45-1192x746.jpg';
    }

    /**
     * 可见性
     * @return string
     */
    public function getVisibilityAttribute(): string
    {
        $uids = $this->uids;
        return $this->getVisibility()[$uids]?:self::auth;
    }
}