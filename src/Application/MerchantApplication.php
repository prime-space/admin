<?php namespace App\Application;

use App\Application\Ability\FinderAbility;
use App\Application\Ability\ListingAbility;
use App\Application\Ability\Merchant\MerchantPaymentEntityAbility;
use App\Application\Ability\Merchant\MerchantShopEntityAbility;
use App\Application\Ability\Merchant\MerchantUserEntityAbility;
use App\Application\Ability\PayMethodsAbility;
use App\Application\Ability\QueueAbility;
use App\Application\Ability\StatisticAbility;
use App\Entity\Service;
use App\TagServiceProvider\TagServiceInterface;

class MerchantApplication implements TagServiceInterface, ApplicationInterface
{
    public function getTagServiceName(): string
    {
        return Service::APP_TYPE_MERCHANT;
    }

    public function getAbilityList(): array
    {
        return [
            FinderAbility::TAG_SERVICE_NAME,
            MerchantUserEntityAbility::TAG_SERVICE_NAME,
            MerchantShopEntityAbility::TAG_SERVICE_NAME,
            MerchantPaymentEntityAbility::TAG_SERVICE_NAME,
            PayMethodsAbility::TAG_SERVICE_NAME,
            QueueAbility::TAG_SERVICE_NAME,
            StatisticAbility::TAG_SERVICE_NAME,
            ListingAbility::TAG_SERVICE_NAME,
        ];
    }
}
