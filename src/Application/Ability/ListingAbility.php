<?php namespace App\Application\Ability;

use App\Entity\Service;
use App\Exception\AbilityException;
use App\Exception\FormValidationException;
use App\Exception\ServiceClientRequestException;
use App\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class ListingAbility implements TagServiceInterface, AbilityInterface
{
    const TAG_SERVICE_NAME = 'listing';

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
    public function getList(Request $request, Service $service): array
    {
        try {
            $statistic = $this->serviceClient->getStatistic($service);

            return $statistic;
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }
    }

    /** @throws AbilityException */
    public function getDataMethod(Request $request, Service $service): array
    {
        try {
            $listing = $request->query->get('listing');
            $rowsPerPage = $request->query->getInt('rowsPerPage', null);
            $pageId = $request->query->getInt('pageId', null);
            $params = $request->request->get('form');
            $statistic = $this->serviceClient->getListingData($service, $listing, $rowsPerPage, $pageId, $params);

            return $statistic;
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }
    }

    /** @throws AbilityException
     *  @throws FormValidationException
     */
    public function addMethod(Request $request, Service $service)
    {
        try {
            $listing = $request->query->get('listing');
            $params = $request->request->get('form');
            $statistic = $this->serviceClient->listingAdd($service, $listing, $params);

            return $statistic;
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }
    }
}
