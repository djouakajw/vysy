<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'target_url',
        'file_path',
        'file_type',
        'scans_count',
        'is_active',
    ];

    protected $casts = [
        'scans_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function incrementScan(): void
    {
        $this->increment('scans_count');
    }
}
