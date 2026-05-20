@extends('layouts.app')
@section('title', 'Invoice #' . $deposit->invoice_id)
@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">Invoice Deposit</h1>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-4">
        <div class="text-center">
            <p class="text-sm text-gray-500">Invoice ID</p>
            <p class="font-mono font-bold text-lg">{{ $deposit->invoice_id }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Jumlah</p><p class="font-bold">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</p></div>
            <div><p class="text-gray-500">Fee</p><p>Rp {{ number_format($deposit->fee, 0, ',', '.') }}</p></div>
            <div><p class="text-gray-500">Total Bayar</p><p class="font-bold text-lg">Rp {{ number_format($deposit->total, 0, ',', '.') }}</p></div>
            <div><p class="text-gray-500">Status</p>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    @if($deposit->status === 'paid') bg-green-100 text-green-800
                    @elseif($deposit->status === 'pending') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ ucfirst($deposit->status) }}
                </span>
            </div>
            <div><p class="text-gray-500">Gateway</p><p class="uppercase">{{ $deposit->gateway }}</p></div>
            <div><p class="text-gray-500">Expires</p><p>{{ $deposit->expires_at?->format('d M Y H:i') ?? '-' }}</p></div>
        </div>

        @if($deposit->status === 'pending')
            @if($deposit->qr_url)
                <div class="text-center py-4">
                    <p class="text-sm text-gray-500 mb-2">Scan QRIS untuk membayar:</p>
                    <img src="{{ $deposit->qr_url }}" alt="QRIS" class="mx-auto max-w-xs rounded-lg">
                </div>
            @endif
            @if($deposit->checkout_url)
                <a href="{{ $deposit->checkout_url }}" target="_blank" class="block w-full text-center bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-medium">
                    Bayar Sekarang
                </a>
            @endif
            <p class="text-center text-sm text-gray-500">Pembayaran akan otomatis terverifikasi setelah Anda membayar.</p>
        @elseif($deposit->status === 'paid')
            <div class="p-4 bg-green-50 dark:bg-green-900/30 rounded-lg text-center">
                <p class="text-green-700 dark:text-green-300 font-medium">Pembayaran berhasil! Saldo telah ditambahkan.</p>
            </div>
        @endif

        <a href="{{ route('user.deposits.index') }}" class="block text-center text-primary-600 hover:underline">Kembali ke Riwayat Deposit</a>
    </div>
</div>
@endsection
