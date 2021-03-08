<?php namespace App\Application\Ability\Merchant;

use App\ApiViewCompiler;
use App\Application\Ability\EntityAbilityInterface;
use App\Constraint\Accuracy;
use App\Entity\Service;
use App\Exception\AbilityException;
use App\Exception\FormValidationException;
use App\Exception\ServiceClientRequestException;
use App\Form\Extension\Core\Type\VuetifyCheckboxType;
use App\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use App\VueViewCompiler;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class MerchantUserEntityAbility implements EntityAbilityInterface, TagServiceInterface
{
    const TAG_SERVICE_NAME = 'user';

    public function getEntityName(): string
    {
        return 'User';
    }

    private $serviceClient;
    private $formFactory;
    private $apiViewCompiler;
    private $vueViewCompiler;

    public function __construct(
        ServiceClient $serviceClient,
        FormFactoryInterface $formFactory,
        ApiViewCompiler $apiViewCompiler,
        VueViewCompiler $vueViewCompiler
    ) {
        $this->serviceClient = $serviceClient;
        $this->formFactory = $formFactory;
        $this->apiViewCompiler = $apiViewCompiler;
        $this->vueViewCompiler = $vueViewCompiler;
    }

    /** @throws AbilityException */
    public function pageMethod(Request $request, Service $service, int $userId): array
    {
        try {
            $result = $this->serviceClient->userData($service, $userId);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }

    /** @throws AbilityException */
    public function personalPayoutMethodSettingsMethod(Request $request, Service $service, int $shopId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('hasPersonalFee', VuetifyCheckboxType::class)
            ->add('isEnabled', VuetifyCheckboxType::class)
            ->add('fee', PercentType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                    new Accuracy(2),
                    new Range(['min' => 0, 'max' => 99.99])
                ],
                'type' => 'integer',
            ])
            ->add('payoutMethodId', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                ]
            ]);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new AbilityException();
        }
        try {
            if (!$form->isValid()) {
                throw new FormValidationException();
            }
            $data = ['form' => $form->getData()];
            try {
                $this->serviceClient->userPersonalPayoutMethodSettings($service, $data, $shopId);
            } catch (ServiceClientRequestException $e) {
                $customError = $this->apiViewCompiler->compileErrorView('Cannot connect to service', 'fee');
                throw new AbilityException(['errors' => $customError]);
            }
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));
            throw new AbilityException(['errors' => $errors]);
        }

        return [];
    }

    /** @throws AbilityException */
    public function statusMethod(Request $request, Service $service, int $userId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('isBlocked', VuetifyCheckboxType::class);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new AbilityException();
        }
        try {
            if (!$form->isValid()) {
                throw new FormValidationException();
            }
            $data = $form->getData();
            try {
                $this->serviceClient->userStatus($service, $userId, $data['isBlocked']);
            } catch (ServiceClientRequestException $e) {
                throw new FormValidationException();
            }
        } catch (FormValidationException $e) {
            $customError = $this->apiViewCompiler->compileErrorView('Something went wrong. Try again later');
            throw new AbilityException(['errors' => $customError]);
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
