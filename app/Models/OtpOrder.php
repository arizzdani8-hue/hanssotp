<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpOrder extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'country_id', 'service_id', 'operator_id',
        'provider_id', 'pricing_id', 'provider_order_id', 'phone_number',
        'otp_code', 'status', 'price', 'cost', 'profit', 'is_refunded',
        'full_sms', 'expires_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'profit' => 'decimal:2',
            'is_refunded' => 'boolean',
            'expires_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(OtpPricing::class, 'pricing_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'waiting']);
    }

    public static function generateOrderId(): string
    {
        return 'OTP' . now()->format('ymd') . strtoupper(substr(uniqid(), -6));
    }
}
