@extends('layouts.app')
@section('title', 'Riwayat Transaksi')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Riwayat Transaksi Saldo</h1>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-600">{{ ucfirst($trx->type) }}</span></td>
                        <td class="px-4 py-3">{{ $trx->description }}</td>
                        <td class="px-4 py-3 text-right font-mono {{ $trx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $trx->amount >= 0 ? '+' : '' }}Rp {{ number_format(abs($trx->amount), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($trx->balance_after, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
