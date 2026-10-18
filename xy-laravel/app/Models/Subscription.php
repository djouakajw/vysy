<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'plan',
        'price',
        'currency',
        'status',
        'geniuspay_reference',
        'geniuspay_customer_id',
        'geniuspay_subscription_id',
        'starts_at',
        'expires_at',
        'auto_renew',
        'sites_allowed',
        'products_limit',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
        'sites_allowed' => 'integer',
        'products_limit' => 'integer',
        'features' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}
