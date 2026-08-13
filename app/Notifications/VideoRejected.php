<?php

namespace App\Notifications;

use App\Models\Video;
use Illuminate\Notifications\Notification;

class VideoRejected extends Notification
{
    public function __construct(public Video $video, public ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Video "'.$this->video->title.'" bị từ chối.'
                .($this->reason ? ' Lý do: '.$this->reason : ''),
            'url' => '/my-videos',
            'kind' => 'rejected',
        ];
    }
}
