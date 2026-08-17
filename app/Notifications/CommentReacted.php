<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * FR4: bao cho nguoi viet binh luan khi co ai bay to cam xuc voi binh luan do.
 */
class CommentReacted extends Notification
{
    public function __construct(public User $actor, public Comment $comment, public string $type) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $emoji = CommentReaction::TYPES[$this->type] ?? '👍';
        $excerpt = Str::limit($this->comment->content, 40);

        return [
            'message' => $this->actor->name.' đã bày tỏ cảm xúc '.$emoji.' với bình luận "'.$excerpt.'" của bạn.',
            'url' => '/videos/'.$this->comment->video_id.'#comment-'.$this->comment->id,
            'kind' => 'comment_reaction',
        ];
    }
}
