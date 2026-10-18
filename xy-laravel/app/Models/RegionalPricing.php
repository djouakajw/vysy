<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionalPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'region_code',
        'region_name',
        'price_monthly',
        'price_yearly',
        'currency',
        'original_price_monthly',
        'original_price_yearly',
        'discount_percent',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'currency' => 'string',
        'original_price_monthly' => 'decimal:2',
        'original_price_yearly' => 'decimal:2',
        'discount_percent' => 'integer',
        'is_active' => 'boolean',
    ];

    public const REGIONS = [
        'CM' => 'Cameroun (FCFA)',
        'CI' => 'Côte d\'Ivoire (FCFA)',
        'SN' => 'Sénégal (FCFA)',
        'ML' => 'Mali (FCFA)',
        'BF' => 'Burkina Faso (FCFA)',
        'TG' => 'Togo (FCFA)',
        'BJ' => 'Bénin (FCFA)',
        'NE' => 'Niger (FCFA)',
        'FR' => 'France (EUR)',
        'US' => 'États-Unis (USD)',
        'CD' => 'RD Congo (CDF)',
        'CG' => 'Congo (XAF)',
        'GA' => 'Gabon (XAF)',
        'CM' => 'Cameroun (XAF)',
    ];

    public function scopeRegion($query, $code)
    {
        return $query->where('region_code', $code)->where('is_active', true);
    }

    public function getPriceForPeriod(string $period): float
    {
        return $period === 'year' ? (float) $this->price_yearly : (float) $this->price_monthly;
    }
}
