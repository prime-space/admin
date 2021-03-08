<?php namespace App\Controller;

use App\Entity\Service;
use App\Exception\ControllerException;
use App\Exception\ServiceClientException;
use App\ServiceClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    private $repositoryProvider;
    private $serviceClient;
    private $logger;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        ServiceClient $serviceClient,
        Logger $logger
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->serviceClient = $serviceClient;
        $this->logger = $logger;
    }

    public function action(Request $request, string $method, int $id = null)
    {
        try {
            $auth = $request->headers->get('authorization');
            if ($auth === null) {
                throw new ControllerException(401);
            }
            $secret = $this->serviceClient->getSecret($auth);
            /** @var Service $service */
            $service = $this->repositoryProvider->get(Service::class)->findOneBy(['secret' => $secret]);
            if (null === $service) {
                $this->logger->critical("Unsuccessful API auth with secret '$secret'");
                throw new ControllerException(401);
            }
            $function = [$this->serviceClient, "{$method}Method"];
            if (!is_callable($function)) {
                throw new ControllerException(404);
            }

            $this->logger->info('Call', [
                'serviceId' => $service->id,
                'serviceDomain' => $service->domain,
                'method' => $method,
                'id' => $id,
            ]);

            if ($id !== null) {
                $result = call_user_func($function, $id, $request->request, $service);
            } else {
                $result = call_user_func($function, $request->request, $service);
            }
            $response = new JsonResponse($result);
        } catch (ControllerException|ServiceClientException $e) {
            $response = new JsonResponse([], $e->getCode());
        }

        return $response;
    }
}
