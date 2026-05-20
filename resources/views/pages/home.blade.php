@extends('layouts.app')
@section('title', \App\Models\Setting::get('site_name', config('app.name')) . ' - ' . \App\Models\Setting::get('site_tagline', 'Layanan OTP & Virtual Number'))
@section('content')
<div class="space-y-12">
    {{-- Hero --}}
    <section class="text-center py-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ \App\Models\Setting::get('site_tagline', 'Layanan OTP & Virtual Number Terpercaya') }}</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">Dapatkan nomor virtual untuk verifikasi WhatsApp, Telegram, Gmail, Instagram, Facebook, TikTok, dan platform lainnya dengan harga terjangkau.</p>
        <div class="flex justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-primary-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-primary-700">Daftar Sekarang</a>
            <a href="{{ route('api-docs') }}" class="border border-primary-600 text-primary-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-primary-50 dark:hover:bg-primary-900/20">API Docs</a>
        </div>
    </section>

    {{-- Sliders --}}
    @if($sliders->count() > 0)
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($sliders as $slider)
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm">
            <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}" class="w-full h-48 object-cover">
            <div class="p-4">
                @if($slider->title)<h3 class="font-bold">{{ $slider->title }}</h3>@endif
                @if($slider->description)<p class="text-sm text-gray-500 mt-1">{{ $slider->description }}</p>@endif
                @if($slider->link)<a href="{{ $slider->link }}" class="text-primary-600 text-sm mt-2 inline-block">{{ $slider->button_text ?? 'Lihat' }} →</a>@endif
            </div>
        </div>
        @endforeach
    </section>
    @endif

    {{-- Features --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="font-bold text-lg mb-2">Cepat & Otomatis</h3>
            <p class="text-gray-500 text-sm">Nomor virtual langsung aktif, OTP otomatis ditampilkan tanpa perlu menunggu lama.</p>
        </div>
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="font-bold text-lg mb-2">Aman & Terpercaya</h3>
            <p class="text-gray-500 text-sm">Multi-provider dengan auto fallback untuk memastikan layanan selalu tersedia.</p>
        </div>
        <div class="text-center">
            <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </div>
            <h3 class="font-bold text-lg mb-2">API Reseller</h3>
            <p class="text-gray-500 text-sm">API lengkap dengan HMAC authentication untuk reseller dan integrasi otomatis.</p>
        </div>
    </section>

    {{-- Supported Services --}}
    <section class="text-center">
        <h2 class="text-2xl font-bold mb-6">Layanan Tersedia</h2>
        <div class="flex flex-wrap justify-center gap-4">
            @foreach(['WhatsApp', 'Telegram', 'Gmail', 'Instagram', 'Facebook', 'TikTok', 'Twitter/X', 'Discord', 'Shopee', 'Tokopedia'] as $svc)
                <span class="px-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-sm text-sm font-medium">{{ $svc }}</span>
            @endforeach
        </div>
    </section>
</div>

{{-- Popup Modal --}}
@foreach($popups as $popup)
<div x-data="{ show: true }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 relative">
        <button @click="show = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">&times;</button>
        @if($popup->image)<img src="{{ asset('storage/' . $popup->image) }}" class="w-full rounded-lg mb-4">@endif
        <h3 class="text-lg font-bold mb-2">{{ $popup->title }}</h3>
        <div class="text-sm text-gray-600 dark:text-gray-300">{!! $popup->content !!}</div>
        @if($popup->link)<a href="{{ $popup->link }}" class="mt-4 inline-block bg-primary-600 text-white px-4 py-2 rounded-lg">{{ $popup->button_text ?? 'Lihat' }}</a>@endif
    </div>
</div>
@endforeach
@endsection
