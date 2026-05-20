<?php

namespace App\Services\OtpProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomProvider extends BaseProvider
{
    public function __construct(string $slug, array $config)
    {
        parent::__construct($slug, $config['api_base_url'] ?? '', $config);
    }

    private function client()
    {
        $headers = ['Accept' => 'application/json'];
        if (!empty($this->config['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api_key'];
        }

        return Http::baseUrl($this->baseUrl)
            ->withHeaders($headers)
            ->timeout(30);
    }

    public function createOrder(string $country, string $service, ?string $operator = null): array
    {
        try {
            $endpoint = $this->config['endpoints']['create_order'] ?? '/order/create';
            $response = $this->client()->post($endpoint, [
                'country' => $country,
                'service' => $service,
                'operator' => $operator,
            ]);

            if ($response->failed()) {
                throw new \Exception("Failed to create order on {$this->name}");
            }

            $data = $response->json('data', $response->json());
            return [
                'order_id' => (string) ($data['id'] ?? $data['order_id'] ?? ''),
                'phone_number' => $data['phone'] ?? $data['number'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("{$this->name} createOrder failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getOrderStatus(string $orderId): array
    {
        try {
            $endpoint = str_replace('{id}', $orderId, $this->config['endpoints']['check_order'] ?? "/order/{$orderId}");
            $response = $this->client()->get($endpoint);

            if ($response->failed()) {
                throw new \Exception("Failed to check order on {$this->name}");
            }

            $data = $response->json('data', $response->json());
            return [
                'status' => $data['status'] ?? 'waiting',
                'otp_code' => $data['otp'] ?? $data['code'] ?? null,
                'full_sms' => $data['sms'] ?? null,
                'phone_number' => $data['phone'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("{$this->name} getOrderStatus failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function cancelOrder(string $orderId): array
    {
        try {
            $endpoint = str_replace('{id}', $orderId, $this->config['endpoints']['cancel_order'] ?? "/order/{$orderId}/cancel");
            $response = $this->client()->post($endpoint);
            return ['success' => $response->successful(), 'message' => 'Order cancelled'];
        } catch (\Exception $e) {
            Log::error("{$this->name} cancelOrder failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getBalance(): array
    {
        try {
            $endpoint = $this->config['endpoints']['balance'] ?? '/balance';
            $response = $this->client()->get($endpoint);
            return ['balance' => $response->json('data.balance', $response->json('balance', 0))];
        } catch (\Exception $e) {
            Log::error("{$this->name} getBalance failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
