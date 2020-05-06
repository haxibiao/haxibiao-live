<?php

namespace Haxibiao\Live\Providers;

use App\User;
use Haxibiao\Live\Models\LiveRoom;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerUserMacros();
    }

    protected function registerUserMacros()
    {
         User::macro('liveRoom', function () {
             return $this->hasOne(LiveRoom::class, 'anchor_id');
         });
    }
}
