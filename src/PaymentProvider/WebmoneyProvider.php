<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\PaymentProvider\Webmoney\WmSigner;
use App\TagServiceProvider\TagServiceInterface;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use SimpleXMLElement;

class WebmoneyProvider implements TagServiceInterface, PaymentProviderInterface
{
    private $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getTagServiceName(): string
    {
        return 'webmoney';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['wmid'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        try {
            $signer = new WmSigner($config['wmid'], $config['key'], $config['pass']);
            $reqn = str_replace('.', '', microtime(true));

            $req = new SimpleXMLElement('<w3s.request/>');
            $req->reqn = $reqn;
            $req->wmid = $config['wmid'];
            $req->sign = $signer->sign($reqn);
            $req->getpurses->wmid = $config['wmid'];

            $request = $this->guzzleClient->post(
                'https://w3s.webmoney.ru/asp/XMLPurses.asp',
                [
                    'timeout' => 3,
                    'connect_timeout' => 3,
                    'headers' => [
                        'Content-Type' => 'text/xml; charset=UTF8',
                    ],
                    'body' => $req->asXML(),
                    'verify' => __DIR__ . '/Webmoney/wm.crt',
                ]
            );
            $result = new SimpleXMLElement($request->getBody()->getContents());
            $balance = [];
            if (isset($result->purses->purse)) {
                $lookingForTypes = ['wmz', 'wmr'];
                foreach ($result->purses->purse as $purse) {
                    $purseName = (string) $purse->pursename;
                    $amount = (string) $purse->amount;
                    foreach ($lookingForTypes as $lookingForType) {
                        if ($purseName === $config[$lookingForType]) {
                            $balance[$lookingForType] = (string) $amount;
                        }
                    }
                }
            }

            return $balance;
        } catch (Exception $e) {
            throw new CannotGetBallanceException();
        }
    }
}
