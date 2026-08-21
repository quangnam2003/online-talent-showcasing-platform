<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 3 moc thoi gian dieu khien may trang thai:
// upcoming -> (start_at) nhan bai -> (submission_deadline) binh chon -> (end_at) ket thuc
class Contest extends Model
{
    protected $fillable = ['title', 'description', 'start_at', 'submission_deadline', 'end_at'];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'submission_deadline' => 'datetime',
            'end_at' => 'datetime',
            'announced_at' => 'datetime', // he thong dong dau (contests:announce), khong nam trong $fillable
        ];
    }

    /**
     * Quan quan: cac bai co so phieu CAO NHAT va > 0 (khong phieu → khong quan quan),
     * chu bai con hoat dong. Hoa phieu → dong hang (tra ve nhieu entry).
     */
    public function winners()
    {
        $active = fn ($q) => $q->where('is_active', true);

        $top = (int) $this->entries()->whereHas('user', $active)->max('votes_count');
        if ($top === 0) {
            return collect();
        }

        return $this->entries()
            ->where('votes_count', $top)
            ->whereHas('user', $active)
            ->with(['user', 'video'])
            ->orderBy('created_at')
            ->get();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    /* ---------- Trang thai theo 3 moc thoi gian (FR7) ---------- */

    public function getStatusAttribute(): string
    {
        $now = now();

        if ($now->lt($this->start_at)) {
            return 'upcoming';
        }
        if ($now->lt($this->submission_deadline)) {
            return 'submission';
        }
        if ($now->lt($this->end_at)) {
            return 'voting';
        }

        return 'ended';
    }

    public function isAcceptingSubmissions(): bool
    {
        return $this->status === 'submission';
    }

    public function isVotingOpen(): bool
    {
        return $this->status === 'voting';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'upcoming' => 'Sắp diễn ra',
            'submission' => 'Đang nhận bài',
            'voting' => 'Đang bình chọn',
            'ended' => 'Đã kết thúc',
        };
    }

    // Mau badge Bootstrap tuong ung trang thai
    public function statusColor(): string
    {
        return match ($this->status) {
            'upcoming' => 'secondary',
            'submission' => 'primary',
            'voting' => 'warning',
            'ended' => 'dark',
        };
    }

    // Bang xep hang theo counter cache votes_count
    public function leaderboard()
    {
        return $this->entries()
            ->with(['video', 'user'])
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();
    }
}
