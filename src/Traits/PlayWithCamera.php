<?php

namespace Haxibiao\Live\Traits;

use App\Exceptions\UserException;
use App\Image;
use App\Store;
use Haxibiao\Live\Camera;
use Haxibiao\Live\Live;
use Haxibiao\Live\LiveUtils;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 关联直播能力到User
 */
trait PlayWithCamera
{

    /**
     * 用户有多个实况
     */
    public function cameras(): HasMany
    {
        return $this->hasMany(Camera::class);
    }

    /**
     * 开实况
     */
    public function openCamera($title, $description = null, $cover=null)
    {
        throw_if(!$this->canOpenCamera(), UserException::class, '只有商户才能创建实况!');

        $camera = new Camera();
        if($cover){
            $image = Image::saveImage($cover);
            $camera->cover = $image->url;
        }
        $camera->user_id         = $this->id;
        $camera->store_id        = data_get($this,'store.id');
        $camera->description    = $description;
        $camera->title          = $title;
        $camera->save();

        $streamName = $camera->stream_name;
        $camera->push_stream_key = LiveUtils::genPushKey($streamName);
        $camera->push_stream_url = LiveUtils::getCameraPushUrl();
        $camera->pull_stream_url = LiveUtils::getCameraPullUrl() . "/" . $streamName;
        $camera->stream_name     = $streamName;
        $camera->save();
        return $camera;
    }


    /**
     * 检测用户是否有有资格开启实况
     */
    public function canOpenCamera()
    {
        return $this->store()
            ->where('status',1) //1代表是正常商户
            ->exists();
    }

}
