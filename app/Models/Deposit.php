<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = [
        'invoice_id', 'user_id', 'payment_method_id', 'gateway',
        'amount', 'fee', 'total', 'status', 'gateway_reference',
        'qr_url', 'checkout_url', 'gateway_response', 'paid_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'total' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public static function generateInvoiceId(): string
    {
        return 'DEP' . now()->format('ymd') . strtoupper(substr(uniqid(), -6));
    }
}
