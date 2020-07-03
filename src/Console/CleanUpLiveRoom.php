<?php

namespace Haxibiao\Live\Console;

use Haxibiao\Live\LiveRoom;
use Illuminate\Console\Command;

class CleanUpLiveRoom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时清理直播间数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $live_utils     = LiveUtils::getInstance();
        $streamData     = $live_utils->getStreamOnlineList(1);
        $totalPage      = data_get($streamData, 'TotalPage');
        $streamNameList = [];
        for ($pageNum = 1; $pageNum < $totalPage; $pageNum++) {
            $streamData       = $live_utils->getStreamOnlineList($pageNum);
            $streamNameList[] = data_get($streamData, 'StreamName');
        }
        if (empty($streamNameList)) {
            return null;
        }
        LiveRoom::whereNotIn('id', $streamNameList)->update([
            'status' => LiveRoom::STATUS_OFF,
        ]);
    }
}
