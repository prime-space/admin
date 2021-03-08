<?php namespace App\Controller;

use App\Annotation\AccessRule;
use App\Application\ApplicationInterface;
use App\Entity\Service;
use App\Exception\AbilityException;
use App\Exception\ControllerException;
use App\Exception\FormValidationException;
use App\TagServiceProvider\TagServiceProvider;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class AbilityController extends Controller implements SignControllerInterface
{
    private $tagServiceProvider;
    private $applications;
    private $repositoryProvider;
    private $abilities;

    public function __construct(
        TagServiceProvider $tagServiceProvider,
        iterable $applications,
        RepositoryProvider $repositoryProvider,
        iterable $abilities
    ) {
        $this->tagServiceProvider = $tagServiceProvider;
        $this->applications = $applications;
        $this->repositoryProvider = $repositoryProvider;
        $this->abilities = $abilities;
    }

    /** @AccessRule(name="serviceRule", queryIdKey="serviceId") */
    public function action(Request $request, int $serviceId, string $abilityName, string $method, int $entityId = null)
    {
        try {
            $serviceRepository = $this->repositoryProvider->get(Service::class);
            /** @var Service $service */
            $service = $serviceRepository->findById($serviceId);
            if ($service === null) {
                throw new ControllerException(404);
            }
            /** @var ApplicationInterface $application */
            $application = $this->tagServiceProvider->get($this->applications, $service->appType);
            if ($application === null) {
                throw new ControllerException(404);
            }
            $applicationAbilities = $application->getAbilityList();
            if (!in_array($abilityName, $applicationAbilities, true)) {
                throw new ControllerException(404);
            }
            $ability = $this->tagServiceProvider->get($this->abilities, $abilityName);
            $function = [$ability, "{$method}Method"];
            if (!is_callable($function)) {
                throw new ControllerException(404);
            }
            if ($entityId !== null) {
                $result = call_user_func($function, $request, $service, $entityId);
            } else {
                $result = call_user_func($function, $request, $service);
            }

            return new JsonResponse($result);
        } catch (ControllerException $e) {
            return new JsonResponse([], $e->getCode());
        } catch (AbilityException $abilityException) {
            switch ($abilityException->getCode()) {
                case AbilityException::CODE_BAD_REQUEST:
                    $code = Response::HTTP_BAD_REQUEST;
                    break;
                case AbilityException::CODE_REQUEST_EXCEPTION:
                    $code = Response::HTTP_INTERNAL_SERVER_ERROR;
                    break;
                default:
                    throw new UnexpectedValueException($abilityException->getCode());
            }

            return new JsonResponse($abilityException->getData(), $code);
        } catch (FormValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
