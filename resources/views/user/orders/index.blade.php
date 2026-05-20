@extends('layouts.app')
@section('title', 'Riwayat Order')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Riwayat Order</h1>
        <a href="{{ route('user.orders.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">+ Order Baru</a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Layanan</th>
                        <th class="px-4 py-3 text-left">Negara</th>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">OTP</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Harga</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $order->order_id }}</td>
                        <td class="px-4 py-3">{{ $order->service?->name }}</td>
                        <td class="px-4 py-3">{{ $order->country?->flag_emoji }} {{ $order->country?->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $order->phone_number ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-green-600">{{ $order->otp_code ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($order->status === 'received') bg-green-100 text-green-800
                                @elseif(in_array($order->status, ['waiting', 'pending'])) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">Rp {{ number_format($order->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3"><a href="{{ route('user.orders.show', $order) }}" class="text-primary-600 hover:underline text-xs">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Belum ada order</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
