<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use SimpleXMLElement;

class FreekassaProvider implements TagServiceInterface, PaymentProviderInterface
{
    const API_URL = 'http://www.free-kassa.ru/api.php';
    const API_METHOD_GET_BALANCE = 'get_balance';

    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'freekassa';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['merchantId'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $params = [
                'merchant_id' => $config['merchantId'],
                'action' => self::API_METHOD_GET_BALANCE,
                's' => md5($config['merchantId'].$config['secret2']),
            ];
            $request = $this->guzzleClient->get(
                self::API_URL,
                [
                    'query' => $params,
                    'timeout' => 5,
                    'connect_timeout' => 5,
                ]
            );
            $contents = $request->getBody()->getContents();
            try {
                $data = new SimpleXMLElement($contents);
            } catch (Exception $e) {
                throw new CannotGetBallanceException();
            }
            if (!isset($data->balance[0])) {
                throw new CannotGetBallanceException();
            }
            $balance = (string) $data->balance[0];

            return [$balance];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException();
        }
    }
}
