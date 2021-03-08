<?php namespace App\Application\Ability\Merchant;

use App\AccessRule\ExecutePaymentAccessRule;
use App\AccessRule\RefundAccessRule;
use App\ApiViewCompiler;
use App\Application\Ability\EntityAbilityInterface;
use App\Authenticator;
use App\Entity\Service;
use App\Entity\UserLog;
use App\Exception\AbilityException;
use App\Exception\FormValidationException;
use App\Exception\ServiceClientRequestException;
use App\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use App\VueViewCompiler;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class MerchantPaymentEntityAbility implements EntityAbilityInterface, TagServiceInterface
{
    const TAG_SERVICE_NAME = 'payment';

    public function getEntityName(): string
    {
        return 'Payment';
    }

    private $serviceClient;
    private $formFactory;
    private $vueViewCompiler;
    private $authenticator;
    private $executePaymentAccessRule;
    private $refundAccessRule;
    private $apiViewCompiler;
    private $repositoryProvider;

    public function __construct(
        ServiceClient $serviceClient,
        FormFactoryInterface $formFactory,
        VueViewCompiler $vueViewCompiler,
        Authenticator$authenticator,
        ExecutePaymentAccessRule $executePaymentAccessRule,
        RefundAccessRule $refundAccessRule,
        ApiViewCompiler $apiViewCompiler,
        RepositoryProvider $repositoryProvider
    ) {
        $this->serviceClient = $serviceClient;
        $this->formFactory = $formFactory;
        $this->vueViewCompiler = $vueViewCompiler;
        $this->authenticator = $authenticator;
        $this->executePaymentAccessRule = $executePaymentAccessRule;
        $this->refundAccessRule = $refundAccessRule;
        $this->apiViewCompiler = $apiViewCompiler;
        $this->repositoryProvider = $repositoryProvider;
    }

    /** @throws AbilityException */
    public function pageMethod(Request $request, Service $service, int $paymentId): array
    {
        try {
            $result = $this->serviceClient->payment($service, $paymentId);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }

    /** @throws AbilityException */
    public function refundMethod(Request $request, Service $service, int $paymentId): array
    {
        if (!$this->authenticator->isGranted($this->refundAccessRule)) {
            $customError = $this->apiViewCompiler
                ->compileErrorView('You do not have the permission to refund');
            throw new AbilityException(['errors' => $customError]);
        }
        try {
            $result = $this->serviceClient->paymentRefund($service, $paymentId);
            $userLog = UserLog::create(
                $this->authenticator->getUser()->id,
                UserLog::METHOD_REFUND_PAYMENT,
                $paymentId
            );
            $this->repositoryProvider->get(UserLog::class)->create($userLog);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return [];
    }

    /**
     * @throws AbilityException
     * @throws ServiceClientRequestException
     */
    public function changeEmailMethod(Request $request, Service $service, int $paymentId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('email', TextType::class, ['constraints' => [new Email(), new NotBlank()]]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new AbilityException();
        }

        try {
            if (!$form->isValid()) {
                throw new FormValidationException();
            }
            $formData =['form' => $form->getData()];
            $this->serviceClient->changeEmail($service, $paymentId, $formData);
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));
            throw new AbilityException(['errors' => $errors]);
        }

        return [];
    }

    /** @throws AbilityException */
    public function paymentShotsMethod(Request $request, Service $service, int $paymentId)
    {
        try {
            $paymentShots = $this->serviceClient->paymentShots($service, $paymentId);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }
        $vueSelectPaymentShotViews = [];
        foreach ($paymentShots as $paymentShot) {
            $vueSelectPaymentShotViews[] = [
                'value' => $paymentShot['id'],
                'text' => "{$paymentShot['name']} - #{$paymentShot['id']}",
            ];
        }

        return $vueSelectPaymentShotViews;
    }

    /** @throws AbilityException */
    public function executeMethod(Request $request, Service $service, int $paymentId)
    {
        if (!$this->authenticator->isGranted($this->executePaymentAccessRule)) {
            $customError = $this->apiViewCompiler
                ->compileErrorView('You do not have the permission to execute', 'paymentShotId');
            throw new AbilityException(['errors' => $customError]);
        }
        $formBuilder = $this->formFactory->createBuilder()
            ->add('paymentShotId', IntegerType::class, ['constraints' => [
                new NotNull()
            ]]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new AbilityException();
        }
        try {
            if (!$form->isValid()) {
                throw new FormValidationException();
            }
            $formData = ['form' => $form->getData()];
            try {
                $this->serviceClient->executePayment($service, $paymentId, $formData);
                $userLog = UserLog::create(
                    $this->authenticator->getUser()->id,
                    UserLog::METHOD_EXECUTE_PAYMENT,
                    $paymentId
                );
                $this->repositoryProvider->get(UserLog::class)->create($userLog);
            } catch (ServiceClientRequestException $e) {
                throw new FormValidationException();
            }
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));
            throw new AbilityException(['errors' => $errors]);
        }

        return [];
    }

    public function getTagServiceName(): string
    {
        return self::TAG_SERVICE_NAME;
    }

    public function isShowInMenu(): bool
    {
        return false;
    }
}
