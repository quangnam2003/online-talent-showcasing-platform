<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'avatar', 'bio', 'location', 'achievements',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ---------- Kiem tra vai tro ---------- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCreator(): bool
    {
        return $this->role === 'creator';
    }

    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    /* ---------- Quan he ---------- */

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Nhung nguoi MINH dang theo doi
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    // Nhung nguoi dang theo doi MINH
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')->withTimestamps();
    }

    public function groupPosts(): HasMany
    {
        return $this->hasMany(GroupPost::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function contestEntries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /* ---------- Tien ich ---------- */

    public function isFollowing(User $user): bool
    {
        return $this->following()->whereKey($user->id)->exists();
    }

    // Badge tin nhan chua doc tren navbar (index receiver_id + read_at da co san)
    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }
}
