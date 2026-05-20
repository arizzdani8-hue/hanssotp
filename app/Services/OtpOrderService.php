<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\OtpOrder;
use App\Models\OtpPricing;
use App\Models\OtpProvider;
use App\Models\User;
use App\Services\OtpProviders\ProviderManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OtpOrderService
{
    public function __construct(
        private BalanceService $balanceService,
        private ProviderManager $providerManager,
    ) {}

    public function createOrder(User $user, int $countryId, int $serviceId, ?int $operatorId = null, ?int $providerId = null): OtpOrder
    {
        return DB::transaction(function () use ($user, $countryId, $serviceId, $operatorId, $providerId) {
            $user = User::lockForUpdate()->find($user->id);

            $activeCount = OtpOrder::where('user_id', $user->id)->active()->count();
            if ($activeCount >= $user->max_active_orders) {
                throw new \Exception('Batas order aktif tercapai');
            }

            $recentCount = OtpOrder::where('user_id', $user->id)
                ->where('created_at', '>', now()->subMinute())
                ->count();
            if ($recentCount >= $user->max_orders_per_minute) {
                throw new \Exception('Terlalu banyak order per menit');
            }

            $pricingQuery = OtpPricing::active()
                ->where('country_id', $countryId)
                ->where('service_id', $serviceId);

            if ($operatorId) {
                $pricingQuery->where('operator_id', $operatorId);
            }
            if ($providerId) {
                $pricingQuery->where('provider_id', $providerId);
            }

            $pricing = $pricingQuery->orderBy('sell_price')->first();
            if (!$pricing) {
                throw new \Exception('Layanan tidak tersedia');
            }

            $price = $user->getPriceForService($pricing);

            $flashSale = FlashSale::where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->where(function ($q) use ($serviceId, $countryId) {
                    $q->where('service_id', $serviceId)
                        ->orWhereNull('service_id');
                })
                ->where(function ($q) use ($countryId) {
                    $q->where('country_id', $countryId)
                        ->orWhereNull('country_id');
                })
                ->where(function ($q) {
                    $q->where('max_orders', 0)
                        ->orWhereRaw('used_count < max_orders');
                })
                ->first();

            if ($flashSale) {
                if ($flashSale->discount_percent > 0) {
                    $price -= $price * ($flashSale->discount_percent / 100);
                }
                if ($flashSale->discount_flat > 0) {
                    $price -= $flashSale->discount_flat;
                }
                $price = max($price, 0);
            }

            if ((float) $user->balance < $price) {
                throw new \Exception('Saldo tidak mencukupi');
            }

            $provider = OtpProvider::find($pricing->provider_id);
            $providerInstance = $this->providerManager->resolve($provider);

            try {
                $result = $providerInstance->createOrder(
                    $pricing->provider_country_code ?: $pricing->country->code,
                    $pricing->provider_service_code ?: $pricing->service->slug,
                    $operatorId ? ($pricing->operator->slug ?? null) : null
                );
            } catch (\Exception $e) {
                $fallbackProvider = $this->providerManager->getFallbackProvider($provider->id, $serviceId, $countryId);
                if ($fallbackProvider) {
                    $fallbackInstance = $this->providerManager->resolve($fallbackProvider);
                    $fallbackPricing = OtpPricing::active()
                        ->where('provider_id', $fallbackProvider->id)
                        ->where('service_id', $serviceId)
                        ->where('country_id', $countryId)
                        ->first();
                    $result = $fallbackInstance->createOrder(
                        $fallbackPricing->provider_country_code ?: $pricing->country->code,
                        $fallbackPricing->provider_service_code ?: $pricing->service->slug,
                    );
                    $provider = $fallbackProvider;
                    $pricing = $fallbackPricing;
                    $price = $user->getPriceForService($pricing);
                } else {
                    throw $e;
                }
            }

            $this->balanceService->debit($user, $price, 'order', "Order OTP #{$result['order_id']}", OtpOrder::class, null);

            if ($flashSale) {
                $flashSale->increment('used_count');
            }

            $order = OtpOrder::create([
                'order_id' => OtpOrder::generateOrderId(),
                'user_id' => $user->id,
                'country_id' => $countryId,
                'service_id' => $serviceId,
                'operator_id' => $operatorId,
                'provider_id' => $provider->id,
                'pricing_id' => $pricing->id,
                'provider_order_id' => $result['order_id'],
                'phone_number' => $result['phone_number'],
                'status' => 'waiting',
                'price' => $price,
                'cost' => (float) $pricing->cost_price,
                'profit' => $price - (float) $pricing->cost_price,
                'expires_at' => now()->addMinutes(15),
            ]);

            return $order;
        });
    }

    public function cancelOrder(OtpOrder $order): OtpOrder
    {
        if (!in_array($order->status, ['pending', 'waiting'])) {
            throw new \Exception('Order tidak dapat dibatalkan');
        }

        $provider = OtpProvider::find($order->provider_id);
        $providerInstance = $this->providerManager->resolve($provider);

        if ($order->provider_order_id) {
            $providerInstance->cancelOrder($order->provider_order_id);
        }

        $order->update(['status' => 'cancelled']);
        $this->refundOrder($order);

        return $order->fresh();
    }

    public function refundOrder(OtpOrder $order): void
    {
        if ($order->is_refunded) {
            return;
        }

        $this->balanceService->credit(
            $order->user,
            (float) $order->price,
            'refund',
            "Refund order #{$order->order_id}",
            OtpOrder::class,
            $order->id
        );

        $order->update(['is_refunded' => true, 'status' => 'refunded']);
    }

    public function checkOrderStatus(OtpOrder $order): OtpOrder
    {
        if (!in_array($order->status, ['pending', 'waiting'])) {
            return $order;
        }

        $provider = OtpProvider::find($order->provider_id);
        $providerInstance = $this->providerManager->resolve($provider);

        try {
            $result = $providerInstance->getOrderStatus($order->provider_order_id);

            if ($result['status'] === 'received' && $result['otp_code']) {
                $order->update([
                    'status' => 'received',
                    'otp_code' => $result['otp_code'],
                    'full_sms' => $result['full_sms'] ?? null,
                    'received_at' => now(),
                ]);
            } elseif (in_array($result['status'], ['cancelled', 'expired'])) {
                $order->update(['status' => $result['status']]);
                $this->refundOrder($order);
            }
        } catch (\Exception $e) {
            Log::error('Check order status failed', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order->fresh();
    }
}
