<?php


namespace haxibiao\live\Traits;


use haxibiao\live\LiveRoom;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait PlayWithLive
{
    public function liveRoom(): HasOne
    {
        return $this->hasOne(LiveRoom::class, 'anchor_id');
    }
}