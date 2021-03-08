<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\PaymentProvider\Payeer\CPayeer;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Exception\RequestException;

class PayeerProvider implements TagServiceInterface, PaymentProviderInterface
{
    public function getTagServiceName(): string
    {
        return 'payeer';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['id'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $CPayeer = new CPayeer($config['id'], $config['apiId'], $config['apiKey']);

            if (!$CPayeer->isAuth()) {
                throw new CannotGetBallanceException();
            }

            $request = $CPayeer->getBalance();

            if (count($request['errors']) > 0) {
                throw new CannotGetBallanceException();
            }

            if (!isset($request['balance']['RUB']['BUDGET'])) {
                throw new CannotGetBallanceException();
            }

            return [$request['balance']['RUB']['BUDGET']];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException();
        }
    }
}
