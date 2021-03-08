<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;
use App\PaymentProvider\Advcash\AuthDTO;
use App\PaymentProvider\Advcash\GetBalancesMethod;
use App\PaymentProvider\Advcash\MerchantWebService;
use App\TagServiceProvider\TagServiceInterface;
use Exception;

class AdvcashProvider implements TagServiceInterface, PaymentProviderInterface
{
    public function getTagServiceName(): string
    {
        return 'advcash';
    }

    public function getAccountNumber(array $config): string
    {
        return $config['email'];
    }

    /** @inheritdoc */
    public function getBalance(array $config): array
    {
        throw new CannotGetBallanceException();
        //@TODO
        $merchantWebService = new MerchantWebService();

        $authDto = new AuthDTO();
        $authDto->apiName = $config['apiName'];
        $authDto->accountEmail = $config['email'];
        $authDto->authenticationToken = $merchantWebService->getAuthenticationToken($config['apiPass']);

        $getBalancesMethod = new GetBalancesMethod();
        $getBalancesMethod->arg0 = $authDto;

        try {
            $request = $merchantWebService->getBalances($getBalancesMethod);

            $balances = [];
            foreach ($request->return as $balanceDTO) {
                $id = $balanceDTO->id[0];
                $balances[$id] = $balanceDTO->amount;
            }

            return $balances;
        } catch (Exception $e) {
            throw new CannotGetBallanceException();
        }
    }
}
