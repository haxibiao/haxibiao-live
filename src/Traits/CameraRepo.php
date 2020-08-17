<?php


namespace Haxibiao\Live\Traits;


use App\Image;

trait CameraRepo
{
    public function updateUpdateCamera($title=null, $description = null, $cover=null, $visibility=null){
        if($title){
            $this->title = $title;
        }
        if($description){
            $this->description = $description;
        }
        if($cover){
            $image = Image::saveImage($cover);
            $this->cover = $image->url;
        }

        if( $visibility == 'self'){
            //仅自己可见
            $this->uids = '[]';
        } else if($visibility == 'all'){
            //所有人可见
            $this->uids = '["*"]';
        }
        return$this->refresh();
    }
}