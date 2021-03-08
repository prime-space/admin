<?php namespace App\PaymentProvider;

use App\TagServiceProvider\TagServiceInterface;

class ExchangerProvider implements TagServiceInterface, PaymentProviderInterface
{
    public function getTagServiceName(): string
    {
        return 'exchanger';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['host'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        return [];
    }
}
