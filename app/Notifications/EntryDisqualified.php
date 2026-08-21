<?php

namespace App\Notifications;

use App\Models\Contest;
use App\Models\Video;
use Illuminate\Notifications\Notification;

/**
 * FR7 "Disqualify entry": ban to chuc loai bai du thi (phieu da nhan bi huy).
 */
class EntryDisqualified extends Notification
{
    public function __construct(public Video $video, public Contest $contest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Bài dự thi "'.$this->video->title.'" đã bị ban tổ chức loại khỏi cuộc thi "'.$this->contest->title.'" vì vi phạm thể lệ.',
            'url' => '/contests/'.$this->contest->id,
            'kind' => 'rejected',
        ];
    }
}
