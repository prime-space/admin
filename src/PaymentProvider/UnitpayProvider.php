<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class UnitpayProvider implements TagServiceInterface, PaymentProviderInterface
{
    protected $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'unitpay';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['email'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $params = [
                'method' => 'getPartner',
                'params[login]' => $config['email'],
                'params[secretKey]' => $config['secretKey']
            ];
            $url = 'https://unitpay.ru/api';
            $request = $this->guzzleClient->get(
                $url,
                [
                    'query' => $params,
                    'timeout' => 3,
                    'connect_timeout' => 3,
                ]
            );
            $result = json_decode($request->getBody()->getContents(), true);
            if (!isset($result['result']['balance'])) {
                throw new CannotGetBallanceException("['result']['balance'] is not set");
            }

            return [$result['result']['balance']];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException($e->getMessage());
        }
    }
}
