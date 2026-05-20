@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Dashboard</h1>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Saldo</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['balance'], 0, ',', '.') }}</p>
            <a href="{{ route('user.deposits.create') }}" class="text-sm text-primary-600 hover:underline">+ Deposit</a>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Order</p>
            <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Order Sukses</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['successful_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Deposit</p>
            <p class="text-2xl font-bold">Rp {{ number_format($stats['total_deposits'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('user.orders.create') }}" class="bg-primary-600 text-white rounded-xl p-6 text-center hover:bg-primary-700 transition">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <p class="font-medium">Order OTP</p>
        </a>
        <a href="{{ route('user.deposits.create') }}" class="bg-green-600 text-white rounded-xl p-6 text-center hover:bg-green-700 transition">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="font-medium">Deposit Saldo</p>
        </a>
        <a href="{{ route('user.transactions.index') }}" class="bg-purple-600 text-white rounded-xl p-6 text-center hover:bg-purple-700 transition">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="font-medium">Riwayat Transaksi</p>
        </a>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Order Terbaru</h2>
            <a href="{{ route('user.orders.index') }}" class="text-sm text-primary-600 hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Layanan</th>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">OTP</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $order->order_id }}</td>
                        <td class="px-4 py-3">{{ $order->service?->name }}</td>
                        <td class="px-4 py-3 font-mono">{{ $order->phone_number ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-green-600">{{ $order->otp_code ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($order->status === 'received') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                @elseif(in_array($order->status, ['waiting', 'pending'])) bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">Rp {{ number_format($order->price, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada order</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
