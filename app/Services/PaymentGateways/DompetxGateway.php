<?php

namespace App\Services\PaymentGateways;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DompetxGateway extends BaseGateway
{
    public function __construct()
    {
        parent::__construct('dompetx');
    }

    private function getConfig(): array
    {
        return [
            'api_key' => Setting::get('dompetx_api_key', config('services.dompetx.api_key')),
            'secret_key' => Setting::get('dompetx_secret_key', config('services.dompetx.secret_key')),
            'merchant_id' => Setting::get('dompetx_merchant_id', config('services.dompetx.merchant_id')),
            'api_url' => Setting::get('dompetx_api_url', config('services.dompetx.api_url', 'https://dompetx.com/api/v1')),
        ];
    }

    public function createPayment(float $amount, string $reference, string $description = ''): array
    {
        $config = $this->getConfig();

        try {
            $signature = hash_hmac('sha256', $config['merchant_id'] . $reference . intval($amount), $config['secret_key']);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])->post($config['api_url'] . '/transaction/create', [
                'merchant_id' => $config['merchant_id'],
                'merchant_ref' => $reference,
                'amount' => intval($amount),
                'description' => $description ?: 'Deposit Saldo',
                'signature' => $signature,
                'callback_url' => route('webhook.dompetx'),
                'return_url' => route('user.deposits.index'),
            ]);

            if ($response->failed()) {
                throw new \Exception($response->json('message', 'DOMPETX payment creation failed'));
            }

            $data = $response->json('data', []);
            return [
                'reference' => $data['reference'] ?? null,
                'merchant_ref' => $reference,
                'qr_url' => $data['qr_url'] ?? $data['pay_url'] ?? null,
                'checkout_url' => $data['checkout_url'] ?? $data['pay_url'] ?? null,
                'expires_at' => isset($data['expired_time'])
                    ? date('Y-m-d H:i:s', $data['expired_time'])
                    : now()->addHours(24)->toDateTimeString(),
                'raw_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('DOMPETX createPayment failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyWebhook(array $payload, array $headers): bool
    {
        $config = $this->getConfig();
        $receivedSignature = $headers['x-signature'] ?? $headers['X-Signature'] ?? ($payload['signature'] ?? '');
        $calculatedSignature = hash_hmac('sha256', json_encode($payload), $config['secret_key']);
        return hash_equals($calculatedSignature, $receivedSignature);
    }

    public function parseWebhook(array $body): array
    {
        $statusMap = ['PAID' => 'paid', 'SUCCESS' => 'paid', 'EXPIRED' => 'expired', 'FAILED' => 'failed'];
        return [
            'reference' => $body['merchant_ref'] ?? $body['reference'] ?? null,
            'gateway_reference' => $body['reference'] ?? $body['trx_id'] ?? null,
            'status' => $statusMap[strtoupper($body['status'] ?? '')] ?? 'pending',
            'amount' => $body['amount'] ?? $body['total_amount'] ?? 0,
        ];
    }

    public function fetchChannels(): array
    {
        $config = $this->getConfig();
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
            ])->get($config['api_url'] . '/payment/channels');

            return $response->json('data', []);
        } catch (\Exception $e) {
            Log::error('DOMPETX fetchChannels failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
