<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug'];

    /**
     * Sac mau UI cua the loai — 6 sac trong bang mau mockup (--c-music, --c-dance,
     * --c-visual, --c-acting, --c-food, --c-sport). Slug quen thuoc anh xa co dinh;
     * slug la (admin tao them) duoc bam crc32 de luon nhan mot mau on dinh.
     */
    private const HUE_BY_SLUG = [
        'music' => 'music', 'singing' => 'music', 'beatbox' => 'music',
        'dance' => 'dance', 'dancing' => 'dance',
        'art' => 'visual', 'visual' => 'visual', 'painting' => 'visual', 'drawing' => 'visual', 'design' => 'visual',
        'comedy' => 'acting', 'acting' => 'acting', 'theatre' => 'acting', 'theater' => 'acting', 'drama' => 'acting',
        'photography' => 'food', 'food' => 'food', 'cooking' => 'food', 'cuisine' => 'food',
        'coding' => 'sport', 'tech' => 'sport', 'sport' => 'sport', 'sports' => 'sport', 'fitness' => 'sport',
    ];

    private const HUES = ['music', 'dance', 'visual', 'acting', 'food', 'sport'];

    /** Bien CSS mau the loai, vd "var(--c-music)" — dung cho style="--cat: …". */
    public function colorVar(): string
    {
        $hue = self::HUE_BY_SLUG[$this->slug]
            ?? self::HUES[crc32((string) $this->slug) % count(self::HUES)];

        return "var(--c-{$hue})";
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
