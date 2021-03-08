<?php namespace App;

use App\Entity\PaymentAccount;
use App\Entity\PaymentSystem;
use App\Entity\Service;
use App\Exception\ActionErrorException;
use App\Form\Extension\Core\Type\VuetifyCheckboxType;
use App\Form\Extension\Core\Type\VuetifyIdsType;
use App\PaymentProvider\PaymentProviderInterface;
use App\TagServiceProvider\TagServiceProvider;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class Accountant
{
    private $repositoryProvider;
    private $paymentProviders;
    private $formFactory;
    private $serviceClient;
    private $tagServiceProvider;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        iterable $paymentProviders,
        FormFactory $formFactory,
        ServiceClient $serviceClient,
        TagServiceProvider $tagServiceProvider
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->paymentProviders = $paymentProviders;
        $this->formFactory = $formFactory;
        $this->serviceClient = $serviceClient;
        $this->tagServiceProvider = $tagServiceProvider;
    }

    public function getAccounts()
    {
        /** @var PaymentAccount[] $accounts */
        $accounts = $this->repositoryProvider->get(PaymentAccount::class)->findBy(['isDeleted' => 0]);
        $paymentSystems = $this->repositoryProvider->get(PaymentSystem::class)->findAll('id');
        $services = $this->repositoryProvider->get(Service::class)->findAll('id');

        $accountStat = $this->serviceClient->getAccountStat();

        $accountsView = [];
        foreach ($accounts as $account) {
            $paymentSystem = $paymentSystems[$account->paymentSystemId];
            $service = $services[$account->serviceId];
            /** @var PaymentProviderInterface $paymentProvider */
            $paymentProvider = $this->tagServiceProvider->get(
                $this->paymentProviders,
                $paymentSystem->name
            );
            $turnover = isset($accountStat['turnover'][$account->id])
                ? implode('/', $accountStat['turnover'][$account->id])
                : '';
            $using = isset($accountStat['using'][$account->id])
                ? implode('/', $accountStat['using'][$account->id])
                : '';
            $accountsView[] = [
                'id' => $account->id,
                'name' => $account->name,
                'config' => $account->config,
                'system' => $paymentSystem->name,
                'paymentSystemId' => $account->paymentSystemId,
                'serviceId' => $account->serviceId,
                'service' => $service->domain,
                'account' => $paymentProvider->getAccountNumber($account->config),
                'weight' => $account->weight,
                'enabled' => $account->enabled,
                'assignedIds' => implode(',', $account->assignedIds),
                'isAssigned' => count($account->assignedIds) > 0,
                'using' => $using,
                'turnover' => $turnover,
                'balance' => $account->compileBalanceVueView(),
                'isWhite' => $account->isWhite,
                'isActive' => $account->isActive,
            ];
        }
        $serviceViews = [];
        $paymentSystemViews = [];
        /** @var Service $service */
        foreach ($services as $service) {
            $serviceViews[] = $service->compileVueSelectView();
        }
        /** @var PaymentSystem $paymentSystem */
        foreach ($paymentSystems as $paymentSystem) {
            $paymentSystemViews[] = $paymentSystem->compileVueSelectView();
        }

        return ['accounts' => $accountsView, 'services' => $serviceViews, 'paymentSystems' => $paymentSystemViews];
    }

    /** @throws ActionErrorException */
    public function account(Request $request, int $id = null)
    {
        $errors = [];
        $paymentSystemsIndexedById = $this->repositoryProvider->get(PaymentSystem::class)->findAll('id');
        $paymentSystemsChoice = [];
        $servicesChoice = [];
        foreach ($paymentSystemsIndexedById as $paymentSystem) {
            $paymentSystemsChoice[$paymentSystem->name] = $paymentSystem->id;
        }
        $services = $this->repositoryProvider->get(Service::class)->findAll();
        /** @var Service $service */
        foreach ($services as $service) {
            $servicesChoice[$service->domain] = $service->id;
        }
        $enabledChoice = [
            'Shop' => 'shop',
            'Merchant' => 'merchant',
            'Withdraw' => 'withdraw',
        ];
        $formBuilder = $this->formFactory
            ->createBuilder(FormType::class, null, ['attr' => ['@submit.prevent' => 'createAccount']])
            ->setAction('/account')
            ->add('name', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('serviceId', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Service',
                'choices' => $servicesChoice,
                'constraints' => [new Choice(['choices' => $servicesChoice]), new NotNull()],
            ])
            ->add('weight', NumberType::class, ['constraints' => [new LessThanOrEqual(10), new GreaterThanOrEqual(0)]])
            ->add('paymentSystemId', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Payment system',
                'choices' => $paymentSystemsChoice,
                'constraints' => [new Choice(['choices' => $paymentSystemsChoice]), new NotNull()],
            ])
            ->add('enabled', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Enabled',
                'choices' => $enabledChoice,
                'multiple' => true,
                'constraints' => [new Choice(['choices' => $enabledChoice, 'multiple' => true])],
            ])
            ->add('assignedIds', VuetifyIdsType::class)
            ->add('isWhite', VuetifyCheckboxType::class)
            ->add('isActive', VuetifyCheckboxType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Create',
                'attr' => [':disabled' => 'createAccountSubmitted']
            ]);

        $formData = $request->request->get('form');
        if (isset($formData['paymentSystemId'])) {
            if (isset($paymentSystemsIndexedById[$formData['paymentSystemId']])) {
                $paymentSystemId = $formData['paymentSystemId'];
                $paymentSystemConfigFields = $paymentSystemsIndexedById[$paymentSystemId]->config;
                foreach ($paymentSystemConfigFields as $paymentSystemConfigField) {
                    $formBuilder->add($paymentSystemConfigField, TextType::class, ['constraints' => [new NotBlank()]]);
                }
            }
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new ActionErrorException(400);
        }
        if (!$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addError($errors, $error->getOrigin()->getName(), $error->getMessage());
            }
        }
        if (count($errors) > 0) {
            throw new ActionErrorException(400, $errors);
        }
        $data = $form->getData();
        $config = [];
        $paymentSystemConfigFields = $paymentSystemsIndexedById[$data['paymentSystemId']]->config;
        foreach ($paymentSystemConfigFields as $paymentSystemConfigField) {
            $config[$paymentSystemConfigField] = $data[$paymentSystemConfigField];
        }

        if (null !== $id) {
            $account = $this->repositoryProvider->get(PaymentAccount::class)->findById($id);
            if (null === $account) {
                throw new ActionErrorException(404);
            }

            $account->paymentSystemId = $data['paymentSystemId'];
            $account->serviceId = $data['serviceId'];
            $account->name = $data['name'];
            $account->config = $config;
            $account->weight = $data['weight'];
            $account->enabled = $data['enabled'];
            $account->assignedIds = $data['assignedIds'];
            $account->isWhite = $data['isWhite'];
            $account->isActive = $data['isActive'];

            $this->repositoryProvider->get(PaymentAccount::class)->update($account);
        } else {
            $account = $this->repositoryProvider->get(PaymentAccount::class)->findOneBy(['name' => $data['name']]);
            if ($account !== null) {
                $this->addError($errors, 'name', 'Account with such name already exists');
                throw new ActionErrorException(409, $errors);
            }
            $account = PaymentAccount::create(
                $data['paymentSystemId'],
                $data['serviceId'],
                $data['name'],
                $config,
                $data['weight'],
                $data['enabled'],
                $data['assignedIds'],
                $data['isWhite'],
                $data['isActive']
            );
            $this->repositoryProvider->get(PaymentAccount::class)->create($account);
        }

        if (!$this->serviceClient->syncAccount($account)) {
            $errors = [];
            $this->addError($errors, 'form', 'Updated locally, but cannot sync to services :(');
            throw new ActionErrorException(520, $errors);
        }
    }

    private function addError(&$errors, $name, $message)
    {
        $errors[$name] = $message;
    }
}
