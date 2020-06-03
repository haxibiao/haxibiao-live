<?php

namespace haxibiao\live\Providers;

use haxibiao\live\Console\InstallCommand;
use haxibiao\live\LiveRoom;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class LiveServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'haxibiao\live\Events\NewUserComeIn' => [
            'haxibiao\live\Listeners\NewUserComeIn',
        ],
        'haxibiao\live\Events\UserGoOut'     => [
            'haxibiao\live\Listeners\UserGoOut',
        ],
        'haxibiao\live\Events\CloseRoom'     => [
            'haxibiao\live\Listeners\CloseRoom',
        ],
    ];

    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Register a database migration path.
        $this->loadMigrationsFrom($this->app->make('path.haxibiao-live-sdk.migrations'));

        // 发布配置文件.
        $this->publishes([
            $this->app->make('path.haxibiao-live-sdk.config') . '/tencent-live.php' => $this->app->configPath('tencent-live.php'),
        ], 'live-config');

        // 发布lighhouse graphql文件
        $this->publishes([
            __DIR__ . '/../../graphql/liveRoom' => base_path('graphql/liveRoom'),
        ], 'live-graphql');

        // 发布Nova相关文件
        $this->publishes([
            __DIR__ . '/../Nova' => base_path('app/Nova'),
        ], 'live-nova');

        // Regist Broadcast
        Broadcast::channel('live_room.{liveRoomId}', function ($user, $liveRoomId) {
            $room    = LiveRoom::find($liveRoomId);
            $userIds = Redis::get($room->redis_room_key);
            if ($userIds) {
                $userIds = json_decode($userIds, true);
                return array_search($user->id, $userIds, true);
            }
            return false;
        });

        $this->loadRoutesFrom(
            $this->app->make('path.haxibiao-live-sdk') . '/router.php'
        );

        foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind all of the package paths in the container.
        $this->bindPathsInContainer();

        // Merge config.
        $this->mergeConfigFrom(
            $this->app->make('path.haxibiao-live-sdk.config') . '/tencent-live.php',
            'tencent-live'
        );
        // Register Commands
        $this->registerCommands();

    }

    /**
     * Bind paths in container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        foreach ([
            'path.haxibiao-live-sdk'            => $root = dirname(dirname(__DIR__)),
            'path.haxibiao-live-sdk.config'     => $root . '/config',
            'path.haxibiao-live-sdk.database'   => $database = $root . '/database',
            'path.haxibiao-live-sdk.migrations' => $database . '/migrations',
            'path.haxibiao-live-sdk.seeds'      => $database . '/seeds',
            'path.haxibiao-live-sdk.graphql'    => $database . '/graphql',
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    protected function registerCommands()
    {
        $this->commands([
            InstallCommand::class,
        ]);
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}
