<?php

use Haxibiao\Live\Controllers\Api\CameraController;
use Haxibiao\Live\Controllers\Api\LiveController;
use Illuminate\Contracts\Routing\Registrar as RouteRegisterContract;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function (RouteRegisterContract $api) {
    // @ANY /api/live
    $api->group(['prefix' => 'live'], function (RouteRegisterContract $api) {
        Route::post('/screenShots', LiveController::class . '@screenShots');
        Route::post('/cutOut', LiveController::class . '@cutOutLive');
        Route::post('/recording', LiveController::class . '@recording');
        Route::post('/pushStreamEvent', LiveController::class . '@pushStreamEvent');
    });

    // @ANY /api/camera
    $api->group(['prefix' => 'camera'], function (RouteRegisterContract $api) {
        Route::post('/cutOut', CameraController::class . '@cutOutLive');
        Route::post('/pushStreamEvent', CameraController::class . '@pushStreamEvent');
    });
});
