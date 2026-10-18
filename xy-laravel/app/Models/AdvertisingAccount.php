<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'platform',
        'account_id',
        'account_name',
        'pixel_id',
        'conversion_id',
        'access_token',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public const PLATFORM_META_ADS = 'meta_ads';
    public const PLATFORM_GOOGLE_ADS = 'google_ads';
    public const PLATFORM_TIKTOK_ADS = 'tiktok_ads';
    public const PLATFORM_META_PIXEL = 'meta_pixel';

    public const PLATFORMS = [
        self::PLATFORM_META_ADS => 'Meta Ads',
        self::PLATFORM_GOOGLE_ADS => 'Google Ads',
        self::PLATFORM_TIKTOK_ADS => 'TikTok Ads',
        self::PLATFORM_META_PIXEL => 'Meta Pixel',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function requiresPremium(): bool
    {
        return true;
    }

    public static function minPlanPrice(): int
    {
        return 5000; // Prime et au-dessus
    }
}
