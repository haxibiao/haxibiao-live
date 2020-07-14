<?php

namespace Haxibiao\Live\Console;

use App\Live;
use Haxibiao\Live\LiveRoom;
use Haxibiao\Live\LiveUtils;
use Illuminate\Console\Command;

class FetchLiveRoomStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新 live room 推流状态,更新 status';

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
        $streamData = LiveUtils::getStreamOnlineList(1);
        $totalPage  = data_get($streamData, 'TotalPage');
        // 获取腾讯云给的正在直播的流名称列表
        $streamNameList = [];
        for ($pageNum = 1; $pageNum < $totalPage; $pageNum++) {
            $streamData       = LiveUtils::getStreamOnlineList($pageNum);
            $streamNameList[] = data_get($streamData, 'StreamName');
        }
        if (empty($streamNameList)) {
            return null;
        }
        // 正在直播的状态
        LiveRoom::whereIn('stream_name', $streamNameList)->update([
            'status' => Live::STATUS_ONLINE,
        ]);
        // 已下播的状态
        LiveRoom::whereNotIn('stream_name', $streamNameList)->update([
            'status' => Live::STATUS_OFFLINE,
        ]);
    }
}
