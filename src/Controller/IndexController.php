<?php namespace App\Controller;

use App\AccessRule\AccessRuleProvider;
use App\Application\Ability\Merchant\MerchantShopEntityAbility;
use App\Authenticator;
use App\Entity\PaymentSystem;
use App\Entity\User;
use App\Repository\UserRepository;
use App\ServiceViewCompiler;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{
    private $authenticator;
    private $repositoryProvider;
    private $serviceViewCompiler;
    private $accessRuleProvider;

    public function __construct(
        Authenticator $authenticator,
        RepositoryProvider $repositoryProvider,
        ServiceViewCompiler $serviceViewCompiler,
        AccessRuleProvider $accessRuleProvider
    ) {
        $this->authenticator = $authenticator;
        $this->repositoryProvider = $repositoryProvider;
        $this->serviceViewCompiler = $serviceViewCompiler;
        $this->accessRuleProvider = $accessRuleProvider;
    }

    public function index(Request $request)
    {
        if (!$this->authenticator->isSigned($request)) {
            return $this->redirect('/signIn');
        }
        $currentUser = $this->authenticator->getUser();
        /** @var UserRepository $userRepository */
        $userRepository = $this->repositoryProvider->get(User::class);
        $users = $userRepository->findAll();
        $usersViews = [];
        /** @var User $user */
        foreach ($users as $user) {
            $usersViews[] = $user->compileJsConfigView();
        }
        $accessRulesIndexedById = $this->accessRuleProvider->compileJsConfigViewsIndexedById();
        $paymentSystemRepository = $this->repositoryProvider->get(PaymentSystem::class);

        $jsConfig = [
            'userAccessRights' => $currentUser->accessRights,
            'accessRulesIndexedById' => $accessRulesIndexedById,
            'token' => $currentUser->token,
            'users' => $usersViews,
            'services' => $this->serviceViewCompiler->compileServicesView(),
            'paymentSystems' => $paymentSystemRepository->findAll('id'),
            'shopOnVerificationStatus' => MerchantShopEntityAbility::STATUS_ID_ON_VERIFICATION,
            'shopDeclinedStatus' => MerchantShopEntityAbility::STATUS_ID_DECLINED,
            'shopOkStatus' => MerchantShopEntityAbility::STATUS_ID_OK,
        ];

        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS)),
            'year' => date('Y'),
            'token' => $currentUser->token,
            'appName' => 'admin',
            'pageName' => 'admin',
        ];

        return $this->render('index.html.twig', $data);
    }
}
