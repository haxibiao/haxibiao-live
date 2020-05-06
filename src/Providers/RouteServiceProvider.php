<?php

namespace Haxibiao\Live\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(
            $this->app->make('path.haxibiao-live-sdk').'/router.php'
        );
    }

    public function register()
    {

    }
}
