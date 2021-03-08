<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class GamemoneyProvider implements TagServiceInterface, PaymentProviderInterface
{
    const API_URL_BALANCE = 'https://paygate.gamemoney.com/statistics/balance';

    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'gamemoney';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['projectId'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        $currencies = [
            ['code' => 'RUB', 'sign' => '₽'],
            ['code' => 'USD', 'sign' => '$'],
            ['code' => 'EUR', 'sign' => '€'],
            ['code' => 'BTC', 'sign' => '₿'],
        ];
        $balances = [];
        foreach ($currencies as $currency) {
            $balance = $this->getBalanceByCurrency($currency['code'], $config['projectId'], $config['hmacKey']);
            $balances[$currency['sign']] = "$balance";
        }

        return $balances;
    }

    /** @throws CannotGetBallanceException */
    private function getBalanceByCurrency(string $currency, string $projectId, string $hmacKey): string
    {
        try {
            $params = ['project' => $projectId, 'currency' => $currency];
            ksort($params, SORT_STRING);
            $paramStrings = [];
            foreach ($params as $key => $value) {
                $paramStrings[] = "$key:$value;";
            }
            $params['signature'] = hash_hmac('sha256', implode('', $paramStrings), $hmacKey);
            $request = $this->guzzleClient->post(self::API_URL_BALANCE, [
                'timeout' => 3,
                'connect_timeout' => 3,
                'form_params' =>$params,
            ]);
            $result = json_decode($request->getBody()->getContents(), true);

            if (!array_key_exists('project_balance', $result)) {
                throw new CannotGetBallanceException();
            }

            return $result['project_balance'] ?? '0.00';
        } catch (RequestException $e) {
            throw new CannotGetBallanceException();
        }
    }
}
