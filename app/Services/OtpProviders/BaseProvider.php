<?php

namespace App\Services\OtpProviders;

use Illuminate\Support\Facades\Http;

abstract class BaseProvider
{
    protected string $name;
    protected string $baseUrl;
    protected array $config;

    public function __construct(string $name, string $baseUrl, array $config = [])
    {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->config = $config;
    }

    abstract public function createOrder(string $country, string $service, ?string $operator = null): array;

    abstract public function getOrderStatus(string $orderId): array;

    abstract public function cancelOrder(string $orderId): array;

    abstract public function getBalance(): array;

    public function listServices(string $country): array
    {
        return [];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
