<?php namespace App\Application\Ability;

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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class PayMethodsAbility implements TagServiceInterface, AbilityInterface
{
    const TAG_SERVICE_NAME = 'payMethods';

    private $serviceClient;
    private $formFactory;
    private $vueViewCompiler;

    public function __construct(
        ServiceClient $serviceClient,
        FormFactoryInterface $formFactory,
        VueViewCompiler $vueViewCompiler
    ) {
        $this->serviceClient = $serviceClient;
        $this->formFactory = $formFactory;
        $this->vueViewCompiler = $vueViewCompiler;
    }

    /** @throws AbilityException */
    public function payoutMethodsMethod(Request $request, Service $service): array
    {
        try {
            $result = $this->serviceClient->payoutMethods($service);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }

    /** @throws AbilityException */
    public function payoutMethodSettingsMethod(Request $request, Service $service): array
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('fee', PercentType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                    new Accuracy(2),
                    new GreaterThanOrEqual(0),
                ],
                'type' => 'integer',
            ])
            ->add('isEnabled', VuetifyCheckboxType::class)
            ->add('isDefaultExcluded', VuetifyCheckboxType::class)
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
            $formData = ['form' => $form->getData()];
            try {
                $this->serviceClient->payoutMethodSettings($service, $formData);
            } catch (ServiceClientRequestException $e) {
                $form->get('fee')->addError(new FormError('Cannot connect to service'));
                throw new FormValidationException();
            }
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));
            throw new AbilityException(['errors' => $errors]);
        }

        return [];
    }

    /** @throws AbilityException */
    public function paymentMethodsMethod(Request $request, Service $service): array
    {
        try {
            $result = $this->serviceClient->paymentMethods($service);
        } catch (ServiceClientRequestException $e) {
            throw new AbilityException();
        }

        return $result;
    }

    /** @throws AbilityException */
    public function paymentMethodSettingsMethod(Request $request, Service $service): array
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('fee', PercentType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'fill field']),
                    new Accuracy(2),
                    new GreaterThanOrEqual(0),
                ],
                'type' => 'integer',
            ])
            ->add('isEnabled', VuetifyCheckboxType::class)
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
            $formData = ['form' => $form->getData()];
            try {
                $this->serviceClient->paymentMethodSettings($service, $formData);
            } catch (ServiceClientRequestException $e) {
                $form->get('fee')->addError(new FormError('Cannot connect to service'));
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
        return true;
    }
}
