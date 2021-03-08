<?php namespace App;

use App\AccessRule\ServiceAccessRule;
use App\Application\Ability\AbilityInterface;
use App\Application\ApplicationInterface;
use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\TagServiceProvider\TagServiceProvider;
use Ewll\DBBundle\Repository\RepositoryProvider;

class ServiceViewCompiler
{
    private $repositoryProvider;
    private $tagServiceProvider;
    private $applications;
    private $abilities;
    private $serviceAccessRule;
    private $authenticator;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        TagServiceProvider $tagServiceProvider,
        iterable $applications,
        iterable $abilities,
        ServiceAccessRule $serviceAccessRule,
        Authenticator $authenticator
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->tagServiceProvider = $tagServiceProvider;
        $this->applications = $applications;
        $this->abilities = $abilities;
        $this->serviceAccessRule = $serviceAccessRule;
        $this->authenticator = $authenticator;
    }

    public function compileServicesView()
    {
        $serviceRepository = $this->repositoryProvider->get(Service::class);
        $servicesAbilitiesViews = [];
        $services = $serviceRepository->findAll();
        /** @var Service $service */
        foreach ($services as $service) {
            $isGranted = $this->authenticator->isGranted($this->serviceAccessRule, $service->id);
            $abilitiesInMenu = [];
            if (null !== $service->appType) {
                /** @var ApplicationInterface $application */
                $application = $this->tagServiceProvider->get($this->applications, $service->appType);
                $applicationAbilities = $application->getAbilityList();

                foreach ($applicationAbilities as $applicationAbility) {
                    /** @var AbilityInterface $ability */
                    $ability = $this->tagServiceProvider->get($this->abilities, $applicationAbility);
                    if ($ability !== null && $ability->isShowInMenu()) {
                        $abilitiesInMenu[] = $applicationAbility;
                    }
                }
            }
            $servicesAbilitiesViews[] = $service->compileJsConfigView($abilitiesInMenu, $isGranted);
        }

        return $servicesAbilitiesViews;
    }
}
