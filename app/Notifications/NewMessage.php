<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * FR6: co tin nhan moi → nguoi nhan duoc bao qua chuong thong bao (polling san co
 * trong layout se tu hien toast + tang badge). Chi gui o TIN DAU cua mot "dot"
 * (khi nguoi nhan khong con tin chua doc nao tu nguoi gui) de khoi spam chuong.
 */
class NewMessage extends Notification
{
    public function __construct(public User $sender, public string $preview) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->sender->name.' đã gửi tin nhắn cho bạn: "'.Str::limit($this->preview, 80).'"',
            'url' => '/messages/'.$this->sender->id,
            'kind' => 'message',
        ];
    }
}
