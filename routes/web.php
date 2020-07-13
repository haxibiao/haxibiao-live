<?php

use Haxibiao\Live\Controllers\LiveController;
use Illuminate\Contracts\Routing\Registrar as RouteRegisterContract;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'live'], function (RouteRegisterContract $api) {
    Route::any('/getOnlineLiveRoomList', LiveController::class . '@getOnlineLiveRoomList');
    Route::any('/share/{id}', LiveController::class . '@share');
});
