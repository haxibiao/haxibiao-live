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
    protected $signature = 'live:install {--camera} ';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao-live --camera 选项代表发布实况功能';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $withCamera = $this->option('camera');
        if (!$withCamera) {
            return $this->handleLive();
        }
        return $this->handleCamera();
    }

    private function handleLive(){
        $this->comment("复制 stubs ...");
        copy(__DIR__ . '/stubs/LiveRoom.stub', app_path('LiveRoom.php'));
        copy(__DIR__ . '/stubs/Live.stub', app_path('Live.php'));

        $this->comment('发布资源...');
        $this->call('vendor:publish', ['--tag' => 'live', '--force' => true]);

        $this->comment('迁移数据库变化...');
        $this->call('migrate');

        $this->info('Haxibiao Live 安装直播 successfully.');
    }

    private function handleCamera(){

        $this->comment("复制 camera stubs ...");
        copy(__DIR__ . '/stubs/Camera.stub', app_path('Camera.php'));

        $this->comment('发布资源...');
        $this->call('vendor:publish', ['--tag' => 'camera']);


        $this->comment('迁移Camera数据库变化...');
        $this->call('migrate',[
            '--path' => '/packages/haxibiao/live/database/migrations/cameras',
        ]);

        $this->info('Haxibiao Live 安装实况 successfully.');
    }
}
