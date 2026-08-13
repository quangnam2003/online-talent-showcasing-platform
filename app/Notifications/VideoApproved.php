<?php

namespace App\Notifications;

use App\Models\Video;
use Illuminate\Notifications\Notification;

class VideoApproved extends Notification
{
    public function __construct(public Video $video) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Video "'.$this->video->title.'" đã được duyệt và hiển thị công khai.',
            'url' => '/videos/'.$this->video->id,
            'kind' => 'approved',
        ];
    }
}
