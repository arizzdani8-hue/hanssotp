<?php

namespace App\Services\OtpProviders;

use App\Models\OtpProvider;
use Illuminate\Support\Facades\Log;

class ProviderManager
{
    private array $instances = [];

    public function resolve(OtpProvider $provider): BaseProvider
    {
        if (isset($this->instances[$provider->id])) {
            return $this->instances[$provider->id];
        }

        $config = $provider->config ?? [];

        $instance = match ($provider->slug) {
            '5sim' => new FiveSimProvider($config),
            'herosms' => new HeroSmsProvider($config),
            'ditznesia' => new DitznesiaProvider($config),
            default => new CustomProvider($provider->slug, $config),
        };

        $this->instances[$provider->id] = $instance;
        return $instance;
    }

    public function getFallbackProvider(int $excludeProviderId, int $serviceId, int $countryId): ?OtpProvider
    {
        return OtpProvider::active()
            ->where('id', '!=', $excludeProviderId)
            ->whereHas('pricing', function ($q) use ($serviceId, $countryId) {
                $q->where('service_id', $serviceId)
                    ->where('country_id', $countryId)
                    ->where('is_active', true);
            })
            ->first();
    }
}
