<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class BitcoinProvider implements TagServiceInterface, PaymentProviderInterface
{
    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'bitcoin';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['ip'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $response = $this->rpcRequest($config, 'getwalletinfo');
            if (!isset($response['result']['balance'])) {
                throw new CannotGetBallanceException();
            }

            return [$response['result']['balance']];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException();
        }
    }

    /** @throws RequestException */
    private function rpcRequest(array $config, string $method, array $params = [])
    {
        $url = "http://{$config['ip']}:8332";
        $data = ['jsonrpc' => '1.0', 'id' => 'curltest', 'method' => $method, 'params' => $params];

        $response = $this->guzzleClient->post($url, [
            'body' => json_encode($data),
            'auth' => [$config['user'], $config['pass']],
            'timeout' => 5,
            'connect_timeout' => 5,
        ])->getBody();

        $result = json_decode($response, true);

        return $result;
    }
}
