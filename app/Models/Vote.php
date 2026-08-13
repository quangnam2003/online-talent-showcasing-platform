<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Vote tro vao ENTRY (khong phai video) de phieu chi co gia tri trong pham vi cuoc thi
class Vote extends Model
{
    protected $fillable = ['user_id', 'entry_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ContestEntry::class, 'entry_id');
    }
}
