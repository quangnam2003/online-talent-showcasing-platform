<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ['user_id', 'video_id', 'parent_id', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    // Tu tham chieu: binh luan cha - tra loi 1 cap
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /* ---------- Tien ich cho view ---------- */

    // Da bi sua sau khi dang? (hien nhan "đã chỉnh sửa")
    public function isEdited(): bool
    {
        return $this->updated_at && $this->created_at && $this->updated_at->gt($this->created_at);
    }

    // Gom cam xuc theo loai, sap giam dan theo so luong: ['love' => 3, 'like' => 1]
    public function reactionSummary(): array
    {
        return $this->reactions
            ->countBy('type')
            ->sortDesc()
            ->all();
    }

    // Loai cam xuc nguoi dang xem da chon (null neu chua)
    public function reactionOf(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $this->reactions->firstWhere('user_id', $user->id)?->type;
    }
}
