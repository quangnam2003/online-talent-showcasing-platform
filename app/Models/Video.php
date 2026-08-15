<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'description',
        'file_path', 'mime_type', 'duration', 'thumbnail', 'privacy', 'allow_comments', 'status',
    ];

    protected function casts(): array
    {
        return [
            'allow_comments' => 'boolean',
            'avg_rating' => 'float',
            'trending_score' => 'float',
            'duration' => 'integer',
        ];
    }

    /* ---------- Loai tep (video / am thanh) ---------- */

    // Cac dinh dang tiet muc duoc chap nhan khi upload (FR2) — dung chung cho validate + accept="…"
    public const MEDIA_MIMES = [
        'video/mp4', 'video/quicktime', 'video/webm',
        'audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/x-m4a', 'audio/aac', 'audio/wav', 'audio/x-wav', 'audio/wave',
        'audio/ogg', 'audio/webm', 'audio/flac', 'audio/x-flac',
    ];

    public const AUDIO_EXTENSIONS = ['mp3', 'mpga', 'm4a', 'aac', 'wav', 'ogg', 'oga', 'weba', 'flac'];

    // 'audio' hoac 'video' — uu tien mime_type luu khi upload, du phong theo duoi tep (ban ghi cu)
    public function getMediaTypeAttribute(): string
    {
        if ($this->mime_type && str_starts_with($this->mime_type, 'audio/')) {
            return 'audio';
        }
        $ext = strtolower(pathinfo((string) $this->file_path, PATHINFO_EXTENSION));

        return in_array($ext, self::AUDIO_EXTENSIONS, true) ? 'audio' : 'video';
    }

    public function isAudio(): bool
    {
        return $this->media_type === 'audio';
    }

    // "3:24" / "1:02:05" — null neu chua co thoi luong
    public function durationLabel(): ?string
    {
        if (! $this->duration) {
            return null;
        }
        $h = intdiv($this->duration, 3600);
        $m = intdiv($this->duration % 3600, 60);
        $s = $this->duration % 60;

        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }

    /* ---------- Quan he ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Binh luan goc (khong phai tra loi)
    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function contestEntries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    /* ---------- Scope ---------- */

    // Video nguoi la duoc phep xem: da duyet + cong khai (query nong trang Explore)
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'approved')->where('privacy', 'public');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $q) use ($term) {
            $q->where(function (Builder $sub) use ($term) {
                $sub->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        });
    }

    /* ---------- Tien ich ---------- */

    // Cap nhat counter cache + diem trending sau moi tuong tac (FR3/FR4)
    public function refreshCounters(): void
    {
        $this->likes_count = $this->reactions()->where('liked', true)->count();
        $this->comments_count = $this->comments()->count();
        $this->avg_rating = round((float) $this->reactions()->whereNotNull('stars')->avg('stars'), 2);
        $this->trending_score = $this->views + $this->likes_count * 5 + $this->comments_count * 3;
        $this->save();
    }

    // FR8: private chi chu so huu (va admin) xem duoc; pending/rejected cung vay
    public function isViewableBy(?User $viewer): bool
    {
        if ($viewer && ($viewer->id === $this->user_id || $viewer->isAdmin())) {
            return true;
        }

        return $this->status === 'approved' && $this->privacy === 'public';
    }
}
