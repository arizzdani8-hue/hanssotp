<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'balance',
        'referral_code',
        'referred_by',
        'api_key',
        'role',
        'tier',
        'is_banned',
        'ban_reason',
        'max_active_orders',
        'max_orders_per_minute',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'is_banned' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' || $this->email === config('app.admin_email');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OtpOrder::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function getPriceForService(OtpPricing $pricing): float
    {
        return match ($this->tier) {
            'gold' => (float) $pricing->sell_price_gold ?: (float) $pricing->sell_price,
            'platinum' => (float) $pricing->sell_price_platinum ?: (float) $pricing->sell_price,
            default => (float) $pricing->sell_price,
        };
    }
}
