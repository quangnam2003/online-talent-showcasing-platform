<?php

namespace App\Notifications;

use App\Models\ContestEntry;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * FR7: bao cho chu bai du thi khi co nguoi binh chon.
 */
class NewVote extends Notification
{
    public function __construct(public User $voter, public ContestEntry $entry) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $entry = $this->entry->loadMissing(['video', 'contest']);

        return [
            'message' => $this->voter->name.' vừa bình chọn cho bài dự thi "'.$entry->video?->title.'" của bạn tại cuộc thi "'.$entry->contest?->title.'" (hiện có '.number_format($entry->votes_count).' phiếu).',
            'url' => '/contests/'.$entry->contest_id,
            'kind' => 'vote',
        ];
    }
}
