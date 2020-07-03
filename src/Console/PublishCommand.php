<?php
namespace Haxibiao\Live\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'live:publish {--force : 强制覆盖}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布 haxibiao-live';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // 就配置文件自定义后，不方便覆盖，更新需要单独
        // vendor:publish --tag=task-config --force=true
        $this->call('vendor:publish', [
            '--tag'   => 'live-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'live-nova',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'live-graphql',
            '--force' => $this->option('force'),
        ]);

        // $this->call('vendor:publish', [
        //     '--tag'   => 'live-tests',
        //     '--force' => $this->option('force'),
        // ]);

    }
}
