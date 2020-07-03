<?php

namespace Haxibiao\Live\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UninstallCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'live:uninstall';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '卸载 haxibiao/live';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('主要先删除冗余的表 ...');

        Schema::dropIfExists('live_rooms');
        Schema::dropIfExists('user_lives');

    }

}
