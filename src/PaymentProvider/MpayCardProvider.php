<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class MpayCardProvider extends MpayProvider
{
    const API_URL = 'https://api.mpay.ru';
    const API_METHOD_GET_BALANCE = 'get_balance';

    public function __construct(GuzzleClient $guzzleClient)
    {
        parent::__construct($guzzleClient);
    }

    public function getTagServiceName(): string
    {
        return 'mpay_card';
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
            $content = $request->getBody()->getContents();
            $result = json_decode($content, true);
            if (!isset($result['project_balance'][0]['project_id'])) {
                throw new CannotGetBallanceException("project_balance[0]project_id is not set. " . $content);
            }
            if (!isset($result['project_balance'][0]['project_id'])) {
                throw new CannotGetBallanceException("project_balance[0]project_id is not {$config['partnerId']}. " . $content);
            }
            if (!isset($result['project_balance'][0]['payout_balance'])) {
                throw new CannotGetBallanceException("project_balance.0.payout_balance is not set. " . $content);
            }

            return [$result['project_balance'][0]['payout_balance']];
        } catch (RequestException $e) {
            throw new CannotGetBallanceException($e->getMessage());
        }
    }
}
