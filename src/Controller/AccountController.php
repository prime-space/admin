<?php namespace App\Controller;

use App\Accountant;
use App\Annotation\AccessRule;
use App\Exception\ActionErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends Controller implements SignControllerInterface
{
    private $accountant;

    public function __construct(Accountant $accountant)
    {
        $this->accountant = $accountant;
    }

    /** @AccessRule(name="dashBoardRule") */
    public function accounts(Request $request)
    {
        $accounts = $this->accountant->getAccounts();

        return new JsonResponse($accounts);
    }

    /** @AccessRule(name="dashBoardRule") */
    public function account(Request $request, int $id = null)
    {
        try {
            $this->accountant->account($request, $id);

            return new JsonResponse();
        } catch (ActionErrorException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }
    }
}
