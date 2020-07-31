<?php

namespace Haxibiao\Live\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserAppointmentLive extends Notification
{
    use Queueable;

    protected $user;
    protected $live;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $live)
    {
        $this->user = $user;
        $this->live = $live;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'live_id' => $this->live->id,
            'user_id' => $this->user->id,
        ];
    }
}
