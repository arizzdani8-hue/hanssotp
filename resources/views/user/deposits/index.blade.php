@extends('layouts.app')
@section('title', 'Riwayat Deposit')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Riwayat Deposit</h1>
        <a href="{{ route('user.deposits.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">+ Deposit Baru</a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Gateway</th>
                        <th class="px-4 py-3 text-left">Jumlah</th>
                        <th class="px-4 py-3 text-left">Fee</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($deposits as $deposit)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $deposit->invoice_id }}</td>
                        <td class="px-4 py-3 uppercase">{{ $deposit->gateway }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($deposit->fee, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-bold">Rp {{ number_format($deposit->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($deposit->status === 'paid') bg-green-100 text-green-800
                                @elseif($deposit->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($deposit->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $deposit->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada deposit</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $deposits->links() }}</div>
    </div>
</div>
@endsection
