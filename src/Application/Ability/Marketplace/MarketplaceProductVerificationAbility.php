<?php namespace App\Application\Ability\Marketplace;

use App\Application\Ability\AbilityInterface;
use App\Entity\Service;
use App\Exception\AbilityException;
use App\Service\Exception\BadRequestException;
use App\Service\Exception\ServiceClientRequestException;
use App\Service\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class MarketplaceProductVerificationAbility implements TagServiceInterface, AbilityInterface
{
    const TAG_SERVICE_NAME = 'product-verification';

    private $serviceClient;

    public function __construct(ServiceClient $serviceClient)
    {
        $this->serviceClient = $serviceClient;
    }

    public function getTagServiceName(): string
    {
        return self::TAG_SERVICE_NAME;
    }

    public function isShowInMenu(): bool
    {
        return true;
    }

    /** @throws AbilityException */
    public function userListMethod(Request $request, Service $service): array
    {
        try {
            $result = $this->serviceClient
                ->request($service, '/product/verification/userList', ServiceClient::REQUEST_METHOD_GET);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function productListMethod(Request $request, Service $service, int $id): array
    {
        try {
            $result = $this->serviceClient
                ->request($service, "/product/verification/productList/$id", ServiceClient::REQUEST_METHOD_GET);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }
}
