<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Gop Like + Rating: moi cap (user, video) chi co 1 dong
class Reaction extends Model
{
    protected $fillable = ['user_id', 'video_id', 'liked', 'stars'];

    protected function casts(): array
    {
        return [
            'liked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
