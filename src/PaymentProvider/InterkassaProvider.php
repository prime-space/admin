<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class InterkassaProvider implements TagServiceInterface, PaymentProviderInterface
{
    const API_URL = 'https://api.interkassa.com/v1';

    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'interkassa';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['id'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $authStr = base64_encode("{$config['apiId']}:{$config['apiKey']}");
            $request = $this->guzzleClient->get(
                self::API_URL . '/purse',
                [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'headers' => [
                        'Authorization' => "Basic $authStr",
                    ],
                ]
            );
            $result = json_decode($request->getBody()->getContents(), true);
            if (!isset($result['data'][$config['purseId']]['balance'])) {
                throw new CannotGetBallanceException();
            }
            $balance = bcsub($result['data'][$config['purseId']]['balance'], 0, 2);

            return [$balance];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException();
        }
    }
}
