<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtpProvider extends Model
{
    protected $fillable = [
        'name', 'slug', 'api_base_url', 'is_active', 'priority', 'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(OtpPricing::class, 'provider_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OtpOrder::class, 'provider_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority', 'desc');
    }
}
