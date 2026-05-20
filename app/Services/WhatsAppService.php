<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        $gateway = Setting::get('whatsapp_gateway', 'fonnte');

        return match ($gateway) {
            'fonnte' => $this->sendViaFonnte($phone, $message),
            'mpwa' => $this->sendViaMpwa($phone, $message),
            default => false,
        };
    }

    private function sendViaFonnte(string $phone, string $message): bool
    {
        $token = Setting::get('fonnte_token');
        if (!$token) return false;

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
            ]);

            return $response->json('status', false);
        } catch (\Exception $e) {
            Log::error('Fonnte send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendViaMpwa(string $phone, string $message): bool
    {
        $apiUrl = Setting::get('mpwa_api_url');
        $token = Setting::get('mpwa_token');
        if (!$apiUrl || !$token) return false;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post($apiUrl . '/send-message', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('MPWA send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendOtp(string $phone, string $otpCode): bool
    {
        $siteName = Setting::get('site_name', 'OTP Service');
        $message = "Kode OTP {$siteName} Anda: *{$otpCode}*\n\nJangan berikan kode ini kepada siapapun.\nKode berlaku 5 menit.";
        return $this->send($phone, $message);
    }
}
