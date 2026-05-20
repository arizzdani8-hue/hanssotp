@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Daftar Akun</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">No. WhatsApp (opsional)</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="628xxxxxxxxxx" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Kode Referral (opsional)</label>
                <input type="text" name="referral_code" value="{{ request('ref', old('referral_code')) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium">Daftar</button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-500">Sudah punya akun? <a href="{{ route('login') }}" class="text-primary-600 hover:underline">Login</a></p>
    </div>
</div>
@endsection
