<?php

namespace App\Services\OtpProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DitznesiaProvider extends BaseProvider
{
    public function __construct(array $config)
    {
        parent::__construct('ditznesia', $config['api_base_url'] ?? 'https://api.ditznesia.id/v1', $config);
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

            $response = $this->client()->post('/order', $payload);

            if ($response->failed()) {
                throw new \Exception($response->json('message', 'Failed to create order on Ditznesia'));
            }

            $data = $response->json('data', []);
            return [
                'order_id' => (string) ($data['id'] ?? $data['order_id'] ?? ''),
                'phone_number' => $data['phone'] ?? $data['number'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Ditznesia createOrder failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getOrderStatus(string $orderId): array
    {
        try {
            $response = $this->client()->get("/order/{$orderId}");

            if ($response->failed()) {
                throw new \Exception('Failed to check order status on Ditznesia');
            }

            $data = $response->json('data', []);
            $statusMap = [
                'pending' => 'waiting',
                'waiting' => 'waiting',
                'success' => 'received',
                'received' => 'received',
                'cancelled' => 'cancelled',
                'expired' => 'expired',
            ];

            return [
                'status' => $statusMap[$data['status'] ?? ''] ?? 'waiting',
                'otp_code' => $data['otp'] ?? $data['code'] ?? null,
                'full_sms' => $data['sms'] ?? $data['full_sms'] ?? null,
                'phone_number' => $data['phone'] ?? $data['number'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Ditznesia getOrderStatus failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function cancelOrder(string $orderId): array
    {
        try {
            $response = $this->client()->post("/order/{$orderId}/cancel");
            return ['success' => $response->successful(), 'message' => 'Order cancelled'];
        } catch (\Exception $e) {
            Log::error('Ditznesia cancelOrder failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getBalance(): array
    {
        try {
            $response = $this->client()->get('/balance');
            return ['balance' => $response->json('data.balance', 0)];
        } catch (\Exception $e) {
            Log::error('Ditznesia getBalance failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function listServices(string $country = 'id'): array
    {
        try {
            $response = $this->client()->get('/services', ['country' => $country]);
            return $response->json('data', []);
        } catch (\Exception $e) {
            Log::error('Ditznesia listServices failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
