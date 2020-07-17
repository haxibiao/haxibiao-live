<?php

namespace Haxibiao\Live\Jobs;

use App\User;
use Carbon\Carbon;
use Haxibiao\Live\LiveAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CloseLive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $live;
    protected $closeTime;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($live, Carbon $closeTime)
    {
        $this->live      = $live;
        $this->closeTime = $closeTime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $qb = LiveAction::where([
            'live_id'         => $this->live->id,
            'actionable_type' => 'joins',
        ]);
        $qb->chunkById(100, function ($liveActions) {
            foreach ($liveActions as $liveAction) {
                $liveDuration = $this->closeTime->diffInSeconds($this->live->created_at);
                $user         = $liveAction->user;
                LiveAction::leaveLive($this->live, $user, $liveDuration);
            }
        });
    }
}
