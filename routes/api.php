<?php

use Haxibiao\Live\Controllers\Api\LiveController;
use Illuminate\Contracts\Routing\Registrar as RouteRegisterContract;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function (RouteRegisterContract $api) {
    // @ANY /api/live
    $api->group(['prefix' => 'live'], function (RouteRegisterContract $api) {
        Route::post('/screenShots', LiveController::class . '@screenShots');
        Route::post('/cutOut', LiveController::class . '@cutOutLive');
        Route::post('/recording', LiveController::class . '@recording');
    });
});
