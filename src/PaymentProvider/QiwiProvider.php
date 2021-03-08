<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\TagServiceProvider\TagServiceInterface;
use Exception;
use GuzzleHttp\Client as GuzzleClient;

class QiwiProvider implements TagServiceInterface, PaymentProviderInterface
{
    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'qiwi';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['account'];
    }

    public function getBalance(array $config): array
    {
        try {
            $request = $this->guzzleClient->get(
                "https://edge.qiwi.com/funding-sources/v1/accounts/current",
                [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'headers' => [
                        'Authorization' => "Bearer {$config['token']}",
                    ],
                ]
            );
            $result = json_decode($request->getBody()->getContents(), true);
            $balance = [];
            foreach ($result['accounts'] as $account) {
                if ($account['alias'] === 'qw_wallet_rub') {
                    $balance = [(string) $account['balance']['amount']];
                    break;
                }
            }

            return $balance;
        } catch (Exception $e) {
            throw new CannotGetBallanceException($e->getMessage());
        }
    }
}
