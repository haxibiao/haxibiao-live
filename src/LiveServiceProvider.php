<?php

namespace Haxibiao\Live;

use Haxibiao\Live\Console\InstallCommand;
use Haxibiao\Live\Console\PublishCommand;
use Haxibiao\Live\Console\UninstallCommand;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LiveServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        //安装时 vendor:publish 用
        if ($this->app->runningInConsole()) {
            // 注册 migrations.
            $this->loadMigrationsFrom($this->app->make('path.haxibiao-live.migrations'));

            // 发布配置文件.
            $this->publishes([
                $this->app->make('path.haxibiao-live.config') . '/live.php' => $this->app->configPath('live.php'),
            ], 'live-config');

            // 发布 Nova
            $this->publishes([
                __DIR__ . '/Nova' => base_path('app/Nova'),
            ], 'live-nova');

            // 发布 graphql
            $this->publishes([
                __DIR__ . '/../graphql' => base_path('graphql'),
            ], 'live-graphql');

        }

        //注册Api路由
        $this->registerRoutes();

        foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group($this->apiRoutesConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Get the Nova route group configuration array.
     *
     * @return array
     */
    protected function apiRoutesConfiguration()
    {
        return [
            // 'namespace' => 'Haxibiao\Live\Http\Controllers\Api',
            // 'prefix'    => 'api',
        ];
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
            $this->app->make('path.haxibiao-live.config') . '/live.php',
            'live'
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
            'path.haxibiao-live'            => $root = dirname(__DIR__),
            'path.haxibiao-live.config'     => $root . '/config',
            'path.haxibiao-live.graphql'    => $root . '/graphql',
            'path.haxibiao-live.database'   => $database = $root . '/database',
            'path.haxibiao-live.migrations' => $database . '/migrations',
            'path.haxibiao-live.seeds'      => $database . '/seeds',
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    protected function registerCommands()
    {
        $this->commands([
            InstallCommand::class,
            PublishCommand::class,
            UninstallCommand::class,
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
