<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tu tham chieu kep: sender va receiver deu tro ve users
class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'content'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Toan bo tin nhan giua 2 nguoi (ca 2 chieu)
    public function scopeBetween(Builder $query, User $a, User $b): Builder
    {
        return $query->where(function (Builder $q) use ($a, $b) {
            $q->where('sender_id', $a->id)->where('receiver_id', $b->id);
        })->orWhere(function (Builder $q) use ($a, $b) {
            $q->where('sender_id', $b->id)->where('receiver_id', $a->id);
        });
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
