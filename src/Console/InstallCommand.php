<?php

namespace Haxibiao\Live\Console;


use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;
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
    protected $description = 'Install all of the Live resources';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Live Service Provider...');

        $this->callSilent('vendor:publish', ['--provider' => 'Haxibiao\Live\Providers\AppServiceProvider','--force']);

        $this->comment('Migrate...');

        $this->callSilent('migrate');

        $this->comment('Register Live Service Provider...');
        $this->registerLiveServiceProvider();

        $this->info('Live scaffolding installed successfully.');
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
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL."        Haxibiao\Live\Providers\AppServiceProvider::class,".PHP_EOL,
            file_get_contents(config_path('app.php'))
        ));
    }

    protected function getAppNamespace()
    {
        return Container::getInstance()->getNamespace();
    }
}
