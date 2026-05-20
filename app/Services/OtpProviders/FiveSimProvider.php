<?php

namespace App\Services\OtpProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FiveSimProvider extends BaseProvider
{
    public function __construct(array $config)
    {
        parent::__construct('5sim', 'https://5sim.net/v1', $config);
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
            $op = $operator ?: 'any';
            $response = $this->client()->get("/user/buy/activation/{$country}/{$op}/{$service}");

            if ($response->failed()) {
                throw new \Exception($response->json('message', 'Failed to create order on 5sim'));
            }

            $data = $response->json();
            return [
                'order_id' => (string) $data['id'],
                'phone_number' => isset($data['phone']) ? '+' . $data['phone'] : null,
            ];
        } catch (\Exception $e) {
            Log::error('5sim createOrder failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getOrderStatus(string $orderId): array
    {
        try {
            $response = $this->client()->get("/user/check/{$orderId}");

            if ($response->failed()) {
                throw new \Exception('Failed to check order status on 5sim');
            }

            $data = $response->json();
            $otpCode = null;
            $fullSms = null;

            if (!empty($data['sms'])) {
                $lastSms = end($data['sms']);
                $otpCode = $lastSms['code'] ?? null;
                $fullSms = $lastSms['text'] ?? null;
            }

            $statusMap = [
                'PENDING' => 'waiting',
                'RECEIVED' => 'waiting',
                'CANCELED' => 'cancelled',
                'TIMEOUT' => 'expired',
                'FINISHED' => 'received',
            ];

            return [
                'status' => $statusMap[$data['status']] ?? 'waiting',
                'otp_code' => $otpCode,
                'full_sms' => $fullSms,
                'phone_number' => isset($data['phone']) ? '+' . $data['phone'] : null,
            ];
        } catch (\Exception $e) {
            Log::error('5sim getOrderStatus failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function cancelOrder(string $orderId): array
    {
        try {
            $response = $this->client()->get("/user/cancel/{$orderId}");
            return ['success' => $response->successful(), 'message' => 'Order cancelled'];
        } catch (\Exception $e) {
            Log::error('5sim cancelOrder failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getBalance(): array
    {
        try {
            $response = $this->client()->get('/user/profile');
            return ['balance' => $response->json('balance', 0)];
        } catch (\Exception $e) {
            Log::error('5sim getBalance failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function listServices(string $country = 'indonesia'): array
    {
        try {
            $response = $this->client()->get("/guest/products/{$country}/any");
            $services = [];
            foreach ($response->json() as $key => $val) {
                $services[] = [
                    'code' => $key,
                    'name' => $key,
                    'price' => $val['Price'] ?? 0,
                    'quantity' => $val['Qty'] ?? 0,
                ];
            }
            return $services;
        } catch (\Exception $e) {
            Log::error('5sim listServices failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
