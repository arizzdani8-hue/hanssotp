<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpPricing extends Model
{
    protected $table = 'otp_pricing';

    protected $fillable = [
        'country_id', 'service_id', 'operator_id', 'provider_id',
        'cost_price', 'sell_price', 'sell_price_gold', 'sell_price_platinum',
        'markup_percent', 'stock', 'is_active',
        'provider_service_code', 'provider_country_code',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'sell_price_gold' => 'decimal:2',
            'sell_price_platinum' => 'decimal:2',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(OtpService::class, 'service_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(OtpProvider::class, 'provider_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
