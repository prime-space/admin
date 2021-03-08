<?php namespace App\PaymentProvider;

class YandexCardProvider extends YandexProvider
{
    public function getTagServiceName(): string
    {
        return 'yandex_card';
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        return [];
    }
}
