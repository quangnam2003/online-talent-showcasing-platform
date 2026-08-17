<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use App\Models\Video;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * FR4: bao khi co nguoi tuong tac voi video — binh luan, tra loi binh luan, tha tim hoac cham sao.
 * $type: 'comment' | 'reply' | 'like' | 'rate' ($stars chi dung cho 'rate'; $comment cho comment/reply).
 */
class VideoInteraction extends Notification
{
    public function __construct(
        public User $actor,
        public Video $video,
        public string $type,
        public ?int $stars = null,
        public ?Comment $comment = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $kind = $this->video->isAudio() ? 'bản thu âm' : 'video';
        $title = '"'.$this->video->title.'"';
        $excerpt = $this->comment ? ': "'.Str::limit($this->comment->content, 60).'"' : '';

        $url = '/videos/'.$this->video->id;
        if ($this->comment) {
            $url .= '#comment-'.$this->comment->id;
        }

        return [
            'message' => match ($this->type) {
                'comment' => $this->actor->name.' vừa bình luận '.$kind.' '.$title.' của bạn'.$excerpt,
                'reply' => $this->actor->name.' vừa trả lời bình luận của bạn tại '.$kind.' '.$title.$excerpt,
                'like' => $this->actor->name.' vừa thả tim '.$kind.' '.$title.' của bạn.',
                'rate' => $this->actor->name.' vừa chấm '.$this->stars.' sao '.$kind.' '.$title.' của bạn.',
            },
            'url' => $url,
            'kind' => $this->type,
        ];
    }
}
