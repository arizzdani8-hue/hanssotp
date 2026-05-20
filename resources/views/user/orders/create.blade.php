@extends('layouts.app')
@section('title', 'Order OTP')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Order Nomor OTP</h1>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6" x-data="orderForm()">
        <form method="POST" action="{{ route('user.orders.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Pilih Negara</label>
                <select name="country_id" x-model="countryId" @change="loadPricing()" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
                    <option value="">-- Pilih Negara --</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->flag_emoji }} {{ $country->name }} ({{ $country->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Pilih Layanan</label>
                <select name="service_id" x-model="serviceId" @change="loadPricing()" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
                    <option value="">-- Pilih Layanan --</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Pilih Operator (opsional)</label>
                <select name="operator_id" x-model="operatorId" @change="loadPricing()" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500">
                    <option value="">-- Any/Semua --</option>
                    <template x-for="op in operators" :key="op.id">
                        <option :value="op.id" x-text="op.name"></option>
                    </template>
                </select>
            </div>

            {{-- Pricing Info --}}
            <div x-show="pricing.length > 0" class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                <p class="text-sm font-medium mb-2">Harga Tersedia:</p>
                <template x-for="p in pricing" :key="p.id">
                    <div class="flex justify-between items-center py-1 text-sm">
                        <span x-text="p.provider?.name || 'Provider'"></span>
                        <span class="font-bold text-primary-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(p.user_price)"></span>
                    </div>
                </template>
            </div>

            <div x-show="loading" class="mb-4 text-center text-gray-500">
                <svg class="animate-spin h-5 w-5 mx-auto" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>

            <button type="submit" :disabled="!countryId || !serviceId" class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                Order Sekarang
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function orderForm() {
    return {
        countryId: '',
        serviceId: '',
        operatorId: '',
        operators: [],
        pricing: [],
        loading: false,
        async loadPricing() {
            if (!this.countryId || !this.serviceId) { this.pricing = []; return; }
            this.loading = true;
            try {
                const opRes = await fetch(`{{ route('user.api.operators') }}?country_id=${this.countryId}`);
                const opData = await opRes.json();
                this.operators = opData.data || [];

                let url = `{{ route('user.api.pricing') }}?country_id=${this.countryId}&service_id=${this.serviceId}`;
                if (this.operatorId) url += `&operator_id=${this.operatorId}`;
                const res = await fetch(url);
                const data = await res.json();
                this.pricing = data.data || [];
            } catch (e) { console.error(e); }
            this.loading = false;
        }
    }
}
</script>
@endpush
