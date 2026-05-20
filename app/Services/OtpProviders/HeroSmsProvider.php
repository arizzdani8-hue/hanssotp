<?php

namespace App\Services\OtpProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeroSmsProvider extends BaseProvider
{
    public function __construct(array $config)
    {
        parent::__construct('herosms', $config['api_base_url'] ?? 'https://api.herosms.id/v1', $config);
    }

    private function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . ($this->config['api_key'] ?? ''),
                'Accept' => 'application/json',
            ])
            ->timeout(30);
    }

    public function createOrder(string $country, string $service, ?string $operator = null): array
    {
        try {
            $payload = [
                'country' => $country,
                'service' => $service,
            ];
            if ($operator) {
                $payload['operator'] = $operator;
            }

            $response = $this->client()->post('/order/create', $payload);

            if ($response->failed()) {
                throw new \Exception($response->json('message', 'Failed to create order on HeroSMS'));
            }

            $data = $response->json('data', []);
            return [
                'order_id' => (string) ($data['id'] ?? $data['order_id'] ?? ''),
                'phone_number' => $data['phone'] ?? $data['number'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('HeroSMS createOrder failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getOrderStatus(string $orderId): array
    {
        try {
            $response = $this->client()->get("/order/status/{$orderId}");

            if ($response->failed()) {
                throw new \Exception('Failed to check order status on HeroSMS');
            }

            $data = $response->json('data', []);
            $statusMap = [
                'pending' => 'waiting',
                'waiting' => 'waiting',
                'success' => 'received',
                'cancelled' => 'cancelled',
                'expired' => 'expired',
            ];

            return [
                'status' => $statusMap[$data['status'] ?? ''] ?? 'waiting',
                'otp_code' => $data['otp'] ?? $data['code'] ?? null,
                'full_sms' => $data['sms'] ?? null,
                'phone_number' => $data['phone'] ?? $data['number'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('HeroSMS getOrderStatus failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function cancelOrder(string $orderId): array
    {
        try {
            $response = $this->client()->post("/order/cancel/{$orderId}");
            return ['success' => $response->successful(), 'message' => 'Order cancelled'];
        } catch (\Exception $e) {
            Log::error('HeroSMS cancelOrder failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getBalance(): array
    {
        try {
            $response = $this->client()->get('/balance');
            return ['balance' => $response->json('data.balance', 0)];
        } catch (\Exception $e) {
            Log::error('HeroSMS getBalance failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
