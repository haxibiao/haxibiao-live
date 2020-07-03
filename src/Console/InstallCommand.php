<?php

namespace Haxibiao\Live\Console;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class InstallCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'live:install';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao-live';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment("复制 stubs ...");
        copy(__DIR__ . '/stubs/LiveRoom.stub', app_path('LiveRoom.php'));
        copy(__DIR__ . '/stubs/UserLive.stub', app_path('UserLive.php'));

        $this->comment('发布资源...');
        $this->call('live:publish', ['--force' => true]);

        $this->comment('迁移数据库变化...');
        $this->call('migrate');

        // $this->comment('注册 Service Provider...');
        // $this->registerLiveServiceProvider();

        $this->info('Haxibiao Live 安装 successfully.');
    }

    /**
     * Register the Live service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerLiveServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->getAppNamespace());

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL . "        Haxibiao\Live\LiveServiceProvider::class," . PHP_EOL,
            file_get_contents(config_path('app.php'))
        ));
    }

    protected function getAppNamespace()
    {
        return Container::getInstance()->getNamespace();
    }
}
