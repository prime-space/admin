<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class EnfinsProvider implements TagServiceInterface, PaymentProviderInterface
{
    protected $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'enfins';
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
                'ident' => $config['ident'],
            ];
            $params['sign'] = hash_hmac ('sha1', http_build_query($params), $config['secret']);
            $url = 'https://api.hotpay.money/v1/balance';
            $request = $this->guzzleClient->get(
                $url,
                [
                    'query' => $params,
                    'timeout' => 3,
                    'connect_timeout' => 3,
                ]
            );
            $result = json_decode($request->getBody()->getContents(), true);
            if (!isset($result['result']) || $result['result'] !== true) {
                throw new CannotGetBallanceException("['result'] is not true");
            }

            $balance = null;
            foreach ($result['data'] as $item) {
                if ($item['currency'] === 'RUB') {
                    $balance = $item['balance'];
                }
            }

            if ($balance === null) {
                throw new CannotGetBallanceException("'RUB' item not found");
            }

            return [$balance];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException($e->getMessage());
        }
    }
}
