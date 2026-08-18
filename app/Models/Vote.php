<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Vote tro vao ENTRY (khong phai video) de phieu chi co gia tri trong pham vi cuoc thi.
// contest_id duoc luu kem (du suy ra duoc tu entry) de DB giu unique (user_id, contest_id) = 1 phieu / cuoc thi.
class Vote extends Model
{
    protected $fillable = ['user_id', 'entry_id', 'contest_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ContestEntry::class, 'entry_id');
    }
}
