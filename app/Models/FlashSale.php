<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSale extends Model
{
    protected $fillable = [
        'name', 'service_id', 'country_id', 'discount_percent',
        'discount_flat', 'max_orders', 'used_count', 'is_active',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(OtpService::class, 'service_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function isActive(): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_orders > 0 && $this->used_count >= $this->max_orders) return false;
        return now()->between($this->starts_at, $this->ends_at);
    }
}
