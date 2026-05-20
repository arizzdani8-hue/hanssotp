@extends('layouts.app')
@section('title', 'API Documentation')
@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <h1 class="text-3xl font-bold">API Documentation</h1>
    <p class="text-gray-600 dark:text-gray-400">API Reseller untuk integrasi otomatis. Gunakan API Key dari dashboard Anda.</p>

    {{-- Authentication --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
        <h2 class="text-xl font-bold">Authentication</h2>
        <p>Setiap request harus menyertakan header berikut:</p>
        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg font-mono text-sm overflow-x-auto">
            <pre>X-API-Key: YOUR_API_KEY
X-Timestamp: UNIX_TIMESTAMP
X-Signature: HMAC_SHA256(timestamp + method + path + body, api_key)</pre>
        </div>
        <p class="text-sm text-gray-500">Signature dihitung dari: <code>timestamp + HTTP_METHOD + request_path + request_body</code> menggunakan HMAC-SHA256 dengan API Key sebagai secret.</p>
    </section>

    {{-- Endpoints --}}
    <section class="space-y-6">
        <h2 class="text-xl font-bold">Endpoints</h2>
        <p class="text-sm text-gray-500">Base URL: <code>{{ url('/api/v1') }}</code></p>

        @php
        $endpoints = [
            ['GET', '/balance', 'Cek saldo akun', '{"success": true, "data": {"balance": 150000}}'],
            ['GET', '/countries', 'Daftar negara tersedia', '{"success": true, "data": [{"id": 1, "name": "Indonesia", "code": "id"}]}'],
            ['GET', '/services?country_id=1', 'Daftar layanan per negara', '{"success": true, "data": [{"id": 1, "name": "WhatsApp", "slug": "whatsapp"}]}'],
            ['GET', '/pricing?country_id=1&service_id=1', 'Cek harga layanan', '{"success": true, "data": [{"id": 1, "price": 5000, "provider": "5sim"}]}'],
            ['POST', '/order', 'Order nomor OTP', '{"success": true, "data": {"order_id": "OTP240101ABC123", "phone_number": "+628xxx", "status": "waiting"}}'],
            ['GET', '/order/{orderId}', 'Cek status order', '{"success": true, "data": {"order_id": "OTP240101ABC123", "status": "received", "otp_code": "123456"}}'],
            ['POST', '/order/{orderId}/cancel', 'Cancel order', '{"success": true, "message": "Order dibatalkan, saldo dikembalikan"}'],
        ];
        @endphp

        @foreach($endpoints as [$method, $path, $desc, $response])
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <span class="px-2 py-1 rounded text-xs font-bold {{ $method === 'GET' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">{{ $method }}</span>
                <code class="font-mono text-sm">{{ $path }}</code>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $desc }}</p>
            @if($method === 'POST' && str_contains($path, '/order') && !str_contains($path, 'cancel'))
            <div class="mb-3">
                <p class="text-xs font-medium text-gray-500 mb-1">Request Body:</p>
                <pre class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg text-xs font-mono overflow-x-auto">{"country_id": 1, "service_id": 1, "operator_id": null}</pre>
            </div>
            @endif
            <p class="text-xs font-medium text-gray-500 mb-1">Response:</p>
            <pre class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg text-xs font-mono overflow-x-auto">{{ $response }}</pre>
        </div>
        @endforeach
    </section>

    {{-- Rate Limiting --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <h2 class="text-xl font-bold mb-3">Rate Limiting</h2>
        <p>API dibatasi 60 request per menit per API Key. Header response akan menyertakan:</p>
        <ul class="list-disc list-inside text-sm mt-2 space-y-1 text-gray-600 dark:text-gray-400">
            <li><code>X-RateLimit-Limit</code>: Batas request per menit</li>
            <li><code>X-RateLimit-Remaining</code>: Sisa request tersedia</li>
            <li><code>Retry-After</code>: Waktu tunggu jika limit tercapai (dalam detik)</li>
        </ul>
    </section>

    {{-- Error Codes --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <h2 class="text-xl font-bold mb-3">Error Codes</h2>
        <table class="w-full text-sm">
            <thead><tr><th class="text-left pb-2">Code</th><th class="text-left pb-2">Description</th></tr></thead>
            <tbody class="space-y-1">
                <tr><td class="py-1"><code>401</code></td><td>Invalid API key atau signature</td></tr>
                <tr><td class="py-1"><code>400</code></td><td>Bad request / parameter tidak valid</td></tr>
                <tr><td class="py-1"><code>402</code></td><td>Saldo tidak mencukupi</td></tr>
                <tr><td class="py-1"><code>404</code></td><td>Resource tidak ditemukan</td></tr>
                <tr><td class="py-1"><code>429</code></td><td>Rate limit exceeded</td></tr>
                <tr><td class="py-1"><code>500</code></td><td>Server error</td></tr>
            </tbody>
        </table>
    </section>
</div>
@endsection
