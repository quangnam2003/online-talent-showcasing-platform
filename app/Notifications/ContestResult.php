<?php

namespace App\Notifications;

use App\Models\Contest;
use Illuminate\Notifications\Notification;

/**
 * FR7 "Announce winner": ket qua cuoc thi — gui boi lenh `contests:announce` (Scheduler).
 * Nguoi thang nhan loi chuc mung; cac thi sinh khac nhan tin cong bo ket qua.
 */
class ContestResult extends Notification
{
    public function __construct(public Contest $contest, public bool $isWinner, public int $votes) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->isWinner
                ? 'Chúc mừng! Bạn đạt quán quân cuộc thi "'.$this->contest->title.'" với '.$this->votes.' phiếu bình chọn. 🏆'
                : 'Cuộc thi "'.$this->contest->title.'" đã công bố kết quả — cảm ơn bạn đã tham gia, xem bảng xếp hạng tại trang cuộc thi.',
            'url' => '/contests/'.$this->contest->id,
            'kind' => 'contest',
        ];
    }
}
