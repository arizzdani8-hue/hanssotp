<?php

namespace App\Services\PaymentGateways;

abstract class BaseGateway
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function createPayment(float $amount, string $reference, string $description = ''): array;

    abstract public function verifyWebhook(array $payload, array $headers): bool;

    abstract public function parseWebhook(array $body): array;

    public function getName(): string
    {
        return $this->name;
    }
}
