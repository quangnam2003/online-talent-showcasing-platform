<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = ['owner_id', 'name', 'description'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(GroupPost::class);
    }

    // FR5: bang thao luan chi danh cho thanh vien
    public function hasMember(?User $user): bool
    {
        return $user !== null && $this->members()->whereKey($user->id)->exists();
    }
}
