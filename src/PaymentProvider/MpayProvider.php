<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class MpayProvider implements TagServiceInterface, PaymentProviderInterface
{
    const API_URL = 'https://api.mpay.ru';
    const API_METHOD_GET_BALANCE = 'get_balance';

    protected $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'mpay';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['projectId'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $params = ['partner_id' => $config['partnerId']];
            ksort($params, SORT_STRING);
            $paramStrings = [];
            foreach ($params as $key => $value) {
                $paramStrings[] = "$key=$value";
            }
            $params['sign'] = md5(self::API_METHOD_GET_BALANCE . implode('&', $paramStrings) . $config['apiKey']);
            $url = self::API_URL . '/' . self::API_METHOD_GET_BALANCE;
            $request = $this->guzzleClient->get(
                $url,
                [
                    'query' => $params,
                    'timeout' => 3,
                    'connect_timeout' => 3,
                ]
            );
            $result = json_decode($request->getBody()->getContents(), true);
            if (!isset($result['balance'])) {
                throw new CannotGetBallanceException();
            }

            return [$result['balance']];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException();
        }
    }
}
