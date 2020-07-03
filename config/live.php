<?php
return [
    'secret_id'     => 'AKID50fqHTh7kAPPJCEEMPcfUDqKYWP4oOJT',
    'secret_key'    => env('LIVE_SECRET_KEY'),

    //推流key
    'live_key'      => env('LIVE_KEY'),

    //app名称, 意思一个域名下可以配置多个直播APP
    'app_name'      => 'live',
    //推流URL
    'live_push_url' => env('LIVE_PUSH_URL'),
    //拉流地址
    'live_pull_url' => env('LIVE_PULL_URL'),
];
