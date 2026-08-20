<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * FR5: co nguoi moi tham gia nhom → bao cho chu nhom (chi khi attach moi, khong bao trung).
 */
class GroupJoined extends Notification
{
    public function __construct(public User $member, public Group $group) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->member->name.' vừa tham gia nhóm "'.$this->group->name.'" của bạn.',
            'url' => '/groups/'.$this->group->id,
            'kind' => 'group',
        ];
    }
}
