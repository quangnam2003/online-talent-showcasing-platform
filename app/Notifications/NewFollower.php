<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification
{
    public function __construct(public User $follower) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->follower->name.' vừa theo dõi bạn.',
            'url' => '/users/'.$this->follower->id,
            'kind' => 'follower',
        ];
    }
}
