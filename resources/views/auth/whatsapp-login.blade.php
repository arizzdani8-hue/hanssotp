@extends('layouts.app')
@section('title', 'Login WhatsApp')
@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Login via WhatsApp</h2>
        @if(session('otp_sent'))
            <form method="POST" action="{{ route('login.whatsapp.verify') }}">
                @csrf
                <input type="hidden" name="phone" value="{{ session('phone') }}">
                <p class="text-sm text-gray-500 mb-4 text-center">Kode OTP telah dikirim ke {{ session('phone') }}</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Kode OTP</label>
                    <input type="text" name="otp" maxlength="6" required autofocus class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 text-center text-2xl tracking-widest">
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 font-medium">Verifikasi OTP</button>
            </form>
        @else
            <form method="POST" action="{{ route('login.whatsapp.request') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nomor WhatsApp</label>
                    <input type="text" name="phone" placeholder="628xxxxxxxxxx" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 font-medium">Kirim Kode OTP</button>
            </form>
        @endif
        <p class="mt-4 text-center text-sm text-gray-500"><a href="{{ route('login') }}" class="text-primary-600 hover:underline">Login dengan Email</a></p>
    </div>
</div>
@endsection
