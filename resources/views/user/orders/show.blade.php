@extends('layouts.app')
@section('title', 'Order #' . $order->order_id)
@section('content')
<div class="max-w-2xl mx-auto" x-data="orderStatus()">
    <h1 class="text-2xl font-bold mb-6">Detail Order</h1>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Order ID</p>
                <p class="font-mono font-bold">{{ $order->order_id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p x-text="status" class="font-bold" :class="{
                    'text-green-600': status === 'received',
                    'text-yellow-600': status === 'waiting' || status === 'pending',
                    'text-red-600': status === 'cancelled' || status === 'expired'
                }"></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Layanan</p>
                <p>{{ $order->service?->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Negara</p>
                <p>{{ $order->country?->flag_emoji }} {{ $order->country?->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Nomor Virtual</p>
                <p class="font-mono text-lg" x-text="phoneNumber || '-'"></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Harga</p>
                <p class="font-bold">Rp {{ number_format($order->price, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- OTP Code Display --}}
        <div x-show="otpCode" class="p-6 bg-green-50 dark:bg-green-900/30 rounded-xl text-center">
            <p class="text-sm text-green-600 mb-2">Kode OTP</p>
            <p class="text-4xl font-bold font-mono text-green-700 dark:text-green-300 tracking-widest" x-text="otpCode"></p>
        </div>

        {{-- Waiting Animation --}}
        <div x-show="status === 'waiting'" class="p-6 bg-yellow-50 dark:bg-yellow-900/30 rounded-xl text-center">
            <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-yellow-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <p class="text-yellow-700 dark:text-yellow-300">Menunggu SMS/OTP masuk...</p>
            <p class="text-sm text-yellow-600 mt-1">Auto-refresh setiap 5 detik</p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            @if(in_array($order->status, ['pending', 'waiting']))
            <form method="POST" action="{{ route('user.orders.cancel', $order) }}" class="flex-1">
                @csrf
                <button type="submit" onclick="return confirm('Yakin batalkan order?')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">Cancel Order</button>
            </form>
            @endif
            <a href="{{ route('user.orders.index') }}" class="flex-1 text-center bg-gray-200 dark:bg-gray-700 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Kembali</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function orderStatus() {
    return {
        status: '{{ $order->status }}',
        otpCode: '{{ $order->otp_code ?? '' }}',
        phoneNumber: '{{ $order->phone_number ?? '' }}',
        interval: null,
        init() {
            if (['pending', 'waiting'].includes(this.status)) {
                this.interval = setInterval(() => this.check(), 5000);
            }
        },
        async check() {
            try {
                const res = await fetch('{{ route("user.orders.status", $order) }}');
                const data = await res.json();
                this.status = data.status;
                this.otpCode = data.otp_code || '';
                this.phoneNumber = data.phone_number || this.phoneNumber;
                if (!['pending', 'waiting'].includes(data.status)) {
                    clearInterval(this.interval);
                }
            } catch (e) {}
        }
    }
}
</script>
@endpush
