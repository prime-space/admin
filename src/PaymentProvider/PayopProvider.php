<?php namespace App\PaymentProvider;

use App\TagServiceProvider\TagServiceInterface;

class PayopProvider implements TagServiceInterface, PaymentProviderInterface
{
    public function getTagServiceName(): string
    {
        return 'payop';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['publicKey'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        return [];
    }
}
