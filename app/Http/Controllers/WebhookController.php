<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\WebhookLog;
use App\Services\BalanceService;
use App\Services\PaymentGateways\DompetxGateway;
use App\Services\PaymentGateways\PakasirGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function dompetx(Request $request, BalanceService $balanceService)
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        $log = WebhookLog::create([
            'source' => 'dompetx',
            'event' => $payload['status'] ?? 'unknown',
            'payload' => $payload,
            'headers' => $headers,
            'ip_address' => $request->ip(),
        ]);

        $gateway = new DompetxGateway();

        if (!$gateway->verifyWebhook($payload, $request->headers->all())) {
            $log->update(['is_valid' => false, 'response_code' => 401]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $parsed = $gateway->parseWebhook($payload);
        $this->processPayment($parsed, $balanceService, $log);

        return response()->json(['message' => 'OK']);
    }

    public function pakasir(Request $request, BalanceService $balanceService)
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        $log = WebhookLog::create([
            'source' => 'pakasir',
            'event' => $payload['status'] ?? 'unknown',
            'payload' => $payload,
            'headers' => $headers,
            'ip_address' => $request->ip(),
        ]);

        $gateway = new PakasirGateway();

        if (!$gateway->verifyWebhook($payload, $request->headers->all())) {
            $log->update(['is_valid' => false, 'response_code' => 401]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $parsed = $gateway->parseWebhook($payload);
        $this->processPayment($parsed, $balanceService, $log);

        return response()->json(['message' => 'OK']);
    }

    private function processPayment(array $parsed, BalanceService $balanceService, WebhookLog $log): void
    {
        $deposit = Deposit::where('invoice_id', $parsed['reference'])->first();

        if (!$deposit) {
            $log->update(['response_code' => 404, 'response_body' => 'Deposit not found']);
            return;
        }

        if ($deposit->status === 'paid') {
            $log->update(['response_code' => 200, 'response_body' => 'Already processed']);
            return;
        }

        if ($parsed['status'] === 'paid') {
            $deposit->update([
                'status' => 'paid',
                'gateway_reference' => $parsed['gateway_reference'],
                'paid_at' => now(),
            ]);

            $balanceService->credit(
                $deposit->user,
                (float) $deposit->amount,
                'deposit',
                "Deposit #{$deposit->invoice_id}",
                Deposit::class,
                $deposit->id
            );

            $log->update(['response_code' => 200, 'response_body' => 'Payment processed']);
        } elseif (in_array($parsed['status'], ['expired', 'failed'])) {
            $deposit->update(['status' => $parsed['status']]);
            $log->update(['response_code' => 200, 'response_body' => 'Status updated']);
        }
    }
}
