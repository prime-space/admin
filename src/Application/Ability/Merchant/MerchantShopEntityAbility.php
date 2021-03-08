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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class MerchantShopEntityAbility implements EntityAbilityInterface, TagServiceInterface
{
    const TAG_SERVICE_NAME = 'shop';
    const STATUS_ID_OK = 2;
    const STATUS_ID_ON_VERIFICATION = 3;
    const STATUS_ID_DECLINED = 4;

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
    public function pageMethod(Request $request, Service $service, int $shopId): array
    {
        try {
            $result = $this->serviceClient->shop($service, $shopId);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }

    /** @throws AbilityException */
    public function statisticsMethod(Request $request, Service $service, int $shopId): array
    {
        try {
            $result = $this->serviceClient->shopStatistics($service, $shopId);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }

    /** @throws AbilityException */
    public function personalPaymentMethodSettingsMethod(Request $request, Service $service, int $shopId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('isEnabled', VuetifyCheckboxType::class)
            ->add('hasPersonalFee', VuetifyCheckboxType::class)
            ->add('fee', PercentType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                    new Accuracy(2),
                    new Range(['min' => 0, 'max' => 99.99])
                ],
                'type' => 'integer',
            ])
            ->add('paymentMethodId', IntegerType::class, [
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
                //@TODO log event
                $this->serviceClient->shopPersonalPaymentMethodSettings($service, $data, $shopId);
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
    public function considerRequestMethod(Request $request, Service $service, int $shopId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('status', IntegerType::class, ['constraints' => [
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
            $data = $form->getData();
            try {
                //@TODO log event
                $this->serviceClient->shopStatus($service, $shopId, $data['status']);
            } catch (ServiceClientRequestException $e) {
                throw new FormValidationException();
            }
        } catch (FormValidationException $e) {
            $customError = $this->apiViewCompiler->compileErrorView('Something went wrong. Try again later');
            throw new AbilityException(['errors' => $customError]);
        }

        return [];
    }

    /**
     * @throws AbilityException
     */
    public function dailyLimitMethod(Request $request, Service $service, int $shopId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('dailyLimit', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                    new GreaterThanOrEqual(0),
                    new Accuracy(2),
                ],
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
            $formData = ['form' => $form->getData()];
            try {
                $this->serviceClient->shopDailyLimit($service, $shopId, $formData);
            } catch (ServiceClientRequestException $e) {
                $customError = $this->apiViewCompiler->compileErrorView(
                    'Cannot connect to service',
                    'dailyLimit'
                );
                throw new AbilityException(['errors' => $customError]);
            }
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));
            throw new AbilityException(['errors' => $errors]);
        }

        return [];
    }

    /** @throws AbilityException */
    public function domainSchemeChangeMethod(Request $request, Service $service, int $shopId)
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('scheme', ChoiceType::class, [
                'choices' => ['http', 'https'],
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                ],
            ])
            ->add('domain', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                    new Regex(
                        [
                            'pattern' => '/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/',
                            'message' => 'This value is not a valid Domain.',
                        ]
                    ),
                ],
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
            $formData = ['form' => $form->getData()];
            try {
                $this->serviceClient->shopDomainSchemeChange($service, $shopId, $formData);
            } catch (ServiceClientRequestException $e) {
                $customError = $this->apiViewCompiler->compileErrorView(
                    'Cannot connect to service',
                    'domain'
                );
                throw new AbilityException(['errors' => $customError]);
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

    public function getEntityName(): string
    {
        return 'Shop';
    }
}
