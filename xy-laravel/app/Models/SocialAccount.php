<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'platform',
        'account_id',
        'account_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_active',
        'scopes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'scopes' => 'array',
    ];

    public const PLATFORM_META = 'meta';
    public const PLATFORM_GOOGLE = 'google';
    public const PLATFORM_TIKTOK = 'tiktok';
    public const PLATFORM_WHATSAPP = 'whatsapp';
    public const PLATFORM_INSTAGRAM = 'instagram';

    public const PLATFORMS = [
        self::PLATFORM_META => 'Meta (Facebook/Instagram)',
        self::PLATFORM_GOOGLE => 'Google (Ads/Business)',
        self::PLATFORM_TIKTOK => 'TikTok',
        self::PLATFORM_WHATSAPP => 'WhatsApp Business',
        self::PLATFORM_INSTAGRAM => 'Instagram',
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
}
