<?php namespace App\EventListener;

use App\AccessRule\AccessRuleListInterface;
use App\AccessRule\AccessRuleProvider;
use App\Annotation\AccessRule;
use App\Authenticator;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessRuleAnnotationListener
{
    private $reader;
    private $requestStack;
    private $accessRuleProvider;
    private $authenticator;

    public function __construct(
        AnnotationReader $reader,
        RequestStack $stack,
        AccessRuleProvider $accessRuleProvider,
        Authenticator $authenticator
    ) {
        $this->reader = $reader;
        $this->requestStack = $stack;
        $this->accessRuleProvider = $accessRuleProvider;
        $this->authenticator = $authenticator;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        /** @var Controller $controllerObject */
        list($controllerObject, $methodName) = $controller;

        if (!$controllerObject instanceof Controller) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $accessRuleAnnotation = $this->getAccessRuleAnnotation($controllerObject, $methodName);
        if ($accessRuleAnnotation !== null) {
            $accessRule = $this->accessRuleProvider->getByName($accessRuleAnnotation->name);
            if ($accessRule === null) {
                return;
            }
            $id = null;
            if ($accessRule instanceof AccessRuleListInterface) {
                $id = $request->attributes->getInt($accessRuleAnnotation->queryIdKey);
            }
            if (!$this->authenticator->isGranted($accessRule, $id)) {
                throw new AccessDeniedHttpException('Access denied!');
            }
        }
    }

    private function getAccessRuleAnnotation(Controller $controllerObject, string $methodName): ?AccessRule
    {
        $controllerReflectionObject = new ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);
        $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, AccessRule::class);

        return $methodAnnotation;
    }
}
