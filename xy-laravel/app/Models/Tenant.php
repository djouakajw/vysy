<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'custom_domain',
        'language',
        'description',
        'city',
        'country',
        'logo_url',
        'primary_color',
        'status',
        'plan',
        'is_primary',
        'subscription_expires_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
            if (empty($tenant->language)) {
                $tenant->language = 'fr';
            }
            if (empty($tenant->status)) {
                $tenant->status = 'draft';
            }
            if (empty($tenant->primary_color)) {
                $tenant->primary_color = '#00a86b';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'tenant_id')->orderBy('sort_order', 'asc');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'tenant_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'tenant_id');
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'tenant_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function getPublicUrlAttribute(): string
    {
        if ($this->custom_domain) {
            return (str_starts_with($this->custom_domain, 'http') ? '' : 'https://') . $this->custom_domain;
        }
        return 'https://' . $this->slug . '.' . config('app.domain', 'xy-cm.com');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
