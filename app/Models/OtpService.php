<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtpService extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(OtpPricing::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
