<?php

namespace App\Notifications;

use App\Models\Contest;
use App\Models\Video;
use Illuminate\Notifications\Notification;

/**
 * A2/FR7: video bi tu choi trong khi dang du thi → entry bi go khoi cuoc thi
 * (phieu da nhan bi huy, nguoi vote duoc tra lai luot) — bao cho chu bai biet.
 */
class EntryRemoved extends Notification
{
    public function __construct(public Video $video, public Contest $contest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Bài dự thi "'.$this->video->title.'" đã bị gỡ khỏi cuộc thi "'.$this->contest->title.'" vì video bị từ chối khi kiểm duyệt.',
            'url' => '/contests/'.$this->contest->id,
            'kind' => 'rejected',
        ];
    }
}
