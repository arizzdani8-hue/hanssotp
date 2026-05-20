<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Operator;
use App\Models\OtpProvider;
use App\Models\OtpService;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin
        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'referral_code' => strtoupper(Str::random(8)),
            'email_verified_at' => now(),
        ]);

        // Default Countries
        $countries = [
            ['name' => 'Indonesia', 'code' => 'id', 'phone_code' => '+62', 'flag_emoji' => '🇮🇩'],
            ['name' => 'United States', 'code' => 'us', 'phone_code' => '+1', 'flag_emoji' => '🇺🇸'],
            ['name' => 'United Kingdom', 'code' => 'gb', 'phone_code' => '+44', 'flag_emoji' => '🇬🇧'],
            ['name' => 'India', 'code' => 'in', 'phone_code' => '+91', 'flag_emoji' => '🇮🇳'],
            ['name' => 'Russia', 'code' => 'ru', 'phone_code' => '+7', 'flag_emoji' => '🇷🇺'],
            ['name' => 'Philippines', 'code' => 'ph', 'phone_code' => '+63', 'flag_emoji' => '🇵🇭'],
            ['name' => 'Malaysia', 'code' => 'my', 'phone_code' => '+60', 'flag_emoji' => '🇲🇾'],
            ['name' => 'Thailand', 'code' => 'th', 'phone_code' => '+66', 'flag_emoji' => '🇹🇭'],
            ['name' => 'Vietnam', 'code' => 'vn', 'phone_code' => '+84', 'flag_emoji' => '🇻🇳'],
            ['name' => 'China', 'code' => 'cn', 'phone_code' => '+86', 'flag_emoji' => '🇨🇳'],
        ];
        foreach ($countries as $i => $c) {
            Country::firstOrCreate(['code' => $c['code']], array_merge($c, ['is_active' => true, 'sort_order' => $i]));
        }

        // Default Services
        $services = [
            ['name' => 'WhatsApp', 'slug' => 'whatsapp', 'icon' => 'whatsapp'],
            ['name' => 'Telegram', 'slug' => 'telegram', 'icon' => 'telegram'],
            ['name' => 'Gmail', 'slug' => 'google', 'icon' => 'google'],
            ['name' => 'Instagram', 'slug' => 'instagram', 'icon' => 'instagram'],
            ['name' => 'Facebook', 'slug' => 'facebook', 'icon' => 'facebook'],
            ['name' => 'TikTok', 'slug' => 'tiktok', 'icon' => 'tiktok'],
            ['name' => 'Twitter/X', 'slug' => 'twitter', 'icon' => 'twitter'],
            ['name' => 'Discord', 'slug' => 'discord', 'icon' => 'discord'],
            ['name' => 'Shopee', 'slug' => 'shopee', 'icon' => 'shopee'],
            ['name' => 'Tokopedia', 'slug' => 'tokopedia', 'icon' => 'tokopedia'],
            ['name' => 'Grab', 'slug' => 'grab', 'icon' => 'grab'],
            ['name' => 'Gojek', 'slug' => 'gojek', 'icon' => 'gojek'],
        ];
        foreach ($services as $i => $s) {
            OtpService::firstOrCreate(['slug' => $s['slug']], array_merge($s, ['is_active' => true, 'sort_order' => $i]));
        }

        // Default Operators
        $operators = [
            ['name' => 'Any', 'slug' => 'any'],
            ['name' => 'Telkomsel', 'slug' => 'telkomsel'],
            ['name' => 'XL Axiata', 'slug' => 'xl'],
            ['name' => 'Indosat', 'slug' => 'indosat'],
            ['name' => 'Three', 'slug' => 'three'],
            ['name' => 'Smartfren', 'slug' => 'smartfren'],
        ];
        $idCountry = Country::where('code', 'id')->first();
        foreach ($operators as $op) {
            Operator::firstOrCreate(['slug' => $op['slug']], array_merge($op, ['country_id' => $idCountry?->id, 'is_active' => true]));
        }

        // Default Providers
        $providers = [
            ['name' => '5sim.net', 'slug' => '5sim', 'api_base_url' => 'https://5sim.net/v1', 'priority' => 1, 'config' => ['api_key' => '']],
            ['name' => 'Hero SMS', 'slug' => 'herosms', 'api_base_url' => 'https://herosms.com/api/v1', 'priority' => 2, 'config' => ['api_key' => '']],
            ['name' => 'Ditznesia', 'slug' => 'ditznesia', 'api_base_url' => 'https://api.ditznesia.id/v1', 'priority' => 3, 'config' => ['api_key' => '']],
        ];
        foreach ($providers as $p) {
            OtpProvider::firstOrCreate(['slug' => $p['slug']], array_merge($p, ['is_active' => true]));
        }

        // Default Payment Methods
        PaymentMethod::firstOrCreate(['slug' => 'dompetx-qris'], [
            'name' => 'QRIS (DOMPETX)', 'gateway' => 'dompetx', 'channel_code' => 'QRIS',
            'fee_flat' => 0, 'fee_percent' => 0.7, 'min_amount' => 10000, 'max_amount' => 10000000, 'is_active' => true,
        ]);
        PaymentMethod::firstOrCreate(['slug' => 'pakasir-qris'], [
            'name' => 'QRIS (Pakasir)', 'gateway' => 'pakasir', 'channel_code' => 'QRIS',
            'fee_flat' => 0, 'fee_percent' => 0.7, 'min_amount' => 10000, 'max_amount' => 5000000, 'is_active' => true,
        ]);

        // Default Settings
        $settings = [
            'site_name' => 'OTP Service',
            'site_tagline' => 'Layanan OTP & Virtual Number Terpercaya',
            'primary_color' => '#2563eb',
            'whatsapp_gateway' => 'fonnte',
            'turnstile_enabled' => 'false',
        ];
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
