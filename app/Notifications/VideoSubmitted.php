<?php

namespace App\Notifications;

use App\Models\Video;
use Illuminate\Notifications\Notification;

/**
 * FR2/FR8: creator vua gui tiet muc → bao cho quan tri vien (nguoi duyet) biet co muc moi trong hang doi.
 */
class VideoSubmitted extends Notification
{
    public function __construct(public Video $video) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $kind = $this->video->isAudio() ? 'bản thu âm' : 'video';

        return [
            'message' => $this->video->user->name.' vừa gửi '.$kind.' "'.$this->video->title.'" và đang chờ bạn duyệt.',
            'url' => '/admin/videos?status=pending',
            'kind' => 'submitted',
        ];
    }
}
