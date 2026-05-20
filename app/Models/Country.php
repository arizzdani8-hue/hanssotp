<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name', 'code', 'phone_code', 'flag_emoji', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(OtpPricing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
