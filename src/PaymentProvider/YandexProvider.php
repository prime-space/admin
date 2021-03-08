<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\PaymentProvider\Yandex\API;
use App\TagServiceProvider\TagServiceInterface;
use Exception;

class YandexProvider implements TagServiceInterface, PaymentProviderInterface
{
    public function getTagServiceName(): string
    {
        return 'yandex';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['purse'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        $client = new API($config['token']);
        try {
            $accountInfo = $client->accountInfo();

            return [(string) $accountInfo->balance];
        } catch (Exception $e) {
            throw new CannotGetBallanceException();
        }
    }
}
