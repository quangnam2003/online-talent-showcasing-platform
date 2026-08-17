<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Video;
use Illuminate\Notifications\Notification;

/**
 * FR4: bao cho chu video khi co nguoi tuong tac — binh luan, tha tim hoac cham sao.
 * $type: 'comment' | 'like' | 'rate' ($stars chi dung cho 'rate').
 */
class VideoInteraction extends Notification
{
    public function __construct(
        public User $actor,
        public Video $video,
        public string $type,
        public ?int $stars = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $kind = $this->video->isAudio() ? 'bản thu âm' : 'video';
        $title = '"'.$this->video->title.'"';

        return [
            'message' => match ($this->type) {
                'comment' => $this->actor->name.' vừa bình luận '.$kind.' '.$title.' của bạn.',
                'like' => $this->actor->name.' vừa thả tim '.$kind.' '.$title.' của bạn.',
                'rate' => $this->actor->name.' vừa chấm '.$this->stars.' sao '.$kind.' '.$title.' của bạn.',
            },
            'url' => '/videos/'.$this->video->id,
            'kind' => $this->type,
        ];
    }
}
