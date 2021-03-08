<?php namespace App\Application\Ability\Marketplace;

use App\Application\Ability\EntityAbilityInterface;
use App\Entity\Service;
use App\Exception\AbilityException;
use App\Service\Exception\BadRequestException;
use App\Service\Exception\ServiceClientRequestException;
use App\Service\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class MarketplaceProductEntityAbility implements EntityAbilityInterface, TagServiceInterface
{
    const TAG_SERVICE_NAME = 'product';

    public function getEntityName(): string
    {
        return 'Product';
    }

    private $serviceClient;

    public function __construct(
        ServiceClient $serviceClient
    ) {
        $this->serviceClient = $serviceClient;
    }

    public function getTagServiceName(): string
    {
        return self::TAG_SERVICE_NAME;
    }

    public function isShowInMenu(): bool
    {
        return false;
    }

    /** @throws AbilityException */
    public function pageMethod(Request $request, Service $service, int $id): array
    {
        try {
            $result = $this->serviceClient
                ->request($service, "/product/$id", ServiceClient::REQUEST_METHOD_GET);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function objectsMethod(Request $request, Service $service, int $id): array
    {
        try {
            $result = $this->serviceClient
                ->request($service, "/product/$id/objects", ServiceClient::REQUEST_METHOD_GET);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function blockMethod(Request $request, Service $service, int $id): array
    {
        try {
            $data = $request->request->get('form', []);
            $result = $this->serviceClient
                ->request($service, "/product/$id/verification/block", ServiceClient::REQUEST_METHOD_POST, $data);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function unblockMethod(Request $request, Service $service, int $id): array
    {
        try {
            $data = $request->request->get('form', []);
            $result = $this->serviceClient
                ->request($service, "/product/$id/verification/unblock", ServiceClient::REQUEST_METHOD_POST, $data);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function rejectMethod(Request $request, Service $service, int $id): array
    {
        try {
            $data = $request->request->get('form', []);
            $result = $this->serviceClient
                ->request($service, "/product/$id/verification/reject", ServiceClient::REQUEST_METHOD_POST, $data);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function acceptMethod(Request $request, Service $service, int $id): array
    {
        try {
            $result = $this->serviceClient
                ->request($service, "/product/$id/verification/accept", ServiceClient::REQUEST_METHOD_POST);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }
}
