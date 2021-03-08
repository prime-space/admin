<?php namespace App\Controller;

use App\Annotation\AccessRule;
use App\Entity\PayoutMethod;
use App\Exception\ServiceClientRequestException;
use App\Repository\PayoutMethodRepository;
use App\ServiceClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class PayoutController extends Controller implements SignControllerInterface
{
    private $repositoryProvider;
    private $serviceClient;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        ServiceClient $serviceClient
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->serviceClient = $serviceClient;
    }

    /** @AccessRule(name="payoutRule") */
    public function payoutMethods()
    {
        /** @var PayoutMethodRepository $payoutMethodRepository */
        $payoutMethodRepository = $this->repositoryProvider->get(PayoutMethod::class);
        $payoutMethodsViews = $payoutMethodRepository->findWithBalances();

        try {
            $methodsWaitingIndexedByMethod = $this->serviceClient->getPayoutMethodsWaitingIndexedByMethod();
        } catch (ServiceClientRequestException $e) {
            throw new ServiceUnavailableHttpException();
        }

        $views = [];
        foreach ($payoutMethodsViews as $payoutMethodsView) {
            $available = '0';
            foreach ($payoutMethodsView['balances'] as $balances) {
                $balance = $balances[$payoutMethodsView['balanceKey']] ?? '0';
                $available = bcadd($available, $balance, 2);
            }
            $views[] = [
                'name' => $payoutMethodsView['name'],
                'waiting' => $methodsWaitingIndexedByMethod[$payoutMethodsView['id']] ?? '0',
                'available' => $available,
            ];
        }

        return new JsonResponse(['items' => $views]);
    }
}
