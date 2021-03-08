<?php namespace App\Application;

use App\Application\Ability\FinderAbility;
use App\Application\Ability\Marketplace\MarketplaceProductEntityAbility;
use App\Application\Ability\Marketplace\MarketplaceProductVerificationAbility;
use App\Application\Ability\TreeAbility;
use App\Entity\Service;
use App\TagServiceProvider\TagServiceInterface;

class MarketplaceApplication implements TagServiceInterface, ApplicationInterface
{
    public function getTagServiceName(): string
    {
        return Service::APP_TYPE_MARKETPLACE;
    }

    public function getAbilityList(): array
    {
        return [
            FinderAbility::TAG_SERVICE_NAME,
            TreeAbility::TAG_SERVICE_NAME,
            MarketplaceProductVerificationAbility::TAG_SERVICE_NAME,
            MarketplaceProductEntityAbility::TAG_SERVICE_NAME,
        ];
    }
}
