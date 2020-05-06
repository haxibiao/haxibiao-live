<?php

namespace Haxibiao\Live\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\LiveRoom\NewUserComeIn' => [
            'App\Listeners\LiveRoom\NewUserComeIn',
        ],
        'App\Events\LiveRoom\UserGoOut'     => [
            'App\Listeners\LiveRoom\UserGoOut',
        ],
        'App\Events\LiveRoom\CloseRoom'     => [
            'App\Listeners\LiveRoom\CloseRoom',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
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
