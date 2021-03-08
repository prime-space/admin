<?php namespace App\Application\Ability;

use App\Entity\Service;
use App\Exception\AbilityException;
use App\Exception\ServiceClientRequestException;
use App\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class QueueAbility implements TagServiceInterface, AbilityInterface
{
    const TAG_SERVICE_NAME = 'queue';

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
    public function queuesMethod(Request $request, Service $service)
    {
        try {
            $result = $this->serviceClient->queues($service);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }
}
