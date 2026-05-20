@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <label for="remember" class="text-sm">Ingat saya</label>
            </div>
            <button type="submit" class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium">Login</button>
        </form>
        <div class="mt-4 text-center space-y-2">
            <a href="{{ route('login.whatsapp') }}" class="block text-green-600 hover:underline text-sm">Login via WhatsApp OTP</a>
            <p class="text-sm text-gray-500">Belum punya akun? <a href="{{ route('register') }}" class="text-primary-600 hover:underline">Daftar</a></p>
        </div>
    </div>
</div>
@endsection
