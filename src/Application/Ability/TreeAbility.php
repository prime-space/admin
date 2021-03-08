<?php namespace App\Application\Ability;

use App\Application\Ability\Merchant\EntityAbilityInterface;
use App\Entity\Service;
use App\Exception\AbilityException;
use App\Service\Exception\BadRequestException;
use App\Service\Exception\ServiceClientRequestException;
use App\Service\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class TreeAbility implements TagServiceInterface, AbilityInterface
{
    const TAG_SERVICE_NAME = 'tree';

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
    public function dataMethod(Request $request, Service $service): array
    {
        try {
            $result = $this->serviceClient->request($service, '/categories', ServiceClient::REQUEST_METHOD_GET);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function createMethod(Request $request, Service $service): array
    {
        try {
            $data = $request->request->get('form');
            $result = $this->serviceClient->request($service, '/category', ServiceClient::REQUEST_METHOD_POST, $data);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function editMethod(Request $request, Service $service, int $id): array
    {
        try {
            $data = $request->request->get('form');
            $result = $this->serviceClient->request($service, "/category/$id", ServiceClient::REQUEST_METHOD_PUT, $data);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }

    /** @throws AbilityException */
    public function deleteMethod(Request $request, Service $service, int $id): array
    {
        try {
            $result = $this->serviceClient
                ->request($service, "/category/$id", ServiceClient::REQUEST_METHOD_DELETE);

            return $result;
        } catch (BadRequestException $e) {
            throw new AbilityException(['errors' => $e->getErrors()]);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException([], AbilityException::CODE_REQUEST_EXCEPTION);
        }
    }
}
