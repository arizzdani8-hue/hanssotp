@extends('layouts.app')
@section('title', 'Deposit Saldo')
@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">Deposit Saldo</h1>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('user.deposits.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Jumlah Deposit (Rp)</label>
                <input type="number" name="amount" min="10000" max="10000000" step="1000" required placeholder="Minimal Rp 10.000" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
                <div class="flex gap-2 mt-2">
                    @foreach([25000, 50000, 100000, 250000, 500000] as $amount)
                        <button type="button" onclick="document.querySelector('[name=amount]').value={{ $amount }}" class="px-3 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900">{{ number_format($amount/1000) }}K</button>
                    @endforeach
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Metode Pembayaran</label>
                <div class="space-y-2">
                    @foreach($methods as $method)
                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:border-primary-400">
                            <input type="radio" name="payment_method_id" value="{{ $method->id }}" class="mr-3" required>
                            <div>
                                <p class="font-medium">{{ $method->name }}</p>
                                <p class="text-xs text-gray-500">
                                    Fee: Rp {{ number_format($method->fee_flat, 0, ',', '.') }}
                                    @if($method->fee_percent > 0) + {{ $method->fee_percent }}% @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-medium">Buat Invoice Deposit</button>
        </form>
    </div>
</div>
@endsection
