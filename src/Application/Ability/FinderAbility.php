<?php namespace App\Application\Ability;

use App\Application\ApplicationInterface;
use App\Entity\Service;
use App\Exception\AbilityException;
use App\Exception\FormValidationException;
use App\Exception\ServiceClientRequestException;
use App\ServiceClient;
use App\TagServiceProvider\TagServiceInterface;
use App\TagServiceProvider\TagServiceProvider;
use App\VueViewCompiler;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class FinderAbility implements TagServiceInterface, AbilityInterface
{
    const TAG_SERVICE_NAME = 'finder';

    private $formFactory;
    private $vueViewCompiler;
    private $serviceClient;
    private $repositoryProvider;
    private $tagServiceProvider;
    private $applications;
    private $abilities;

    public function __construct(
        FormFactoryInterface $formFactory,
        VueViewCompiler $vueViewCompiler,
        ServiceClient $serviceClient,
        RepositoryProvider $repositoryProvider,
        TagServiceProvider $tagServiceProvider,
        iterable $applications,
        iterable $abilities
    ) {
        $this->formFactory = $formFactory;
        $this->vueViewCompiler = $vueViewCompiler;
        $this->serviceClient = $serviceClient;
        $this->repositoryProvider = $repositoryProvider;
        $this->tagServiceProvider = $tagServiceProvider;
        $this->applications = $applications;
        $this->abilities = $abilities;
    }

    public function getTagServiceName(): string
    {
        return self::TAG_SERVICE_NAME;
    }

    /** @throws AbilityException */
    public function findMethod(Request $request, Service $service): array
    {
        $formBuilder = $this->formFactory->createBuilder()
            ->add('query', TextType::class, ['constraints' => [
                new NotBlank(['message' => 'fill field']),
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
            $formData = $form->getData();
            try {
                $data['entityTypes'] = $this->serviceClient->find($service, $formData['query']);
                /** @var ApplicationInterface $application */
                $application = $this->tagServiceProvider->get($this->applications, $service->appType);
                $abilities = $application->getAbilityList();
                foreach ($abilities as $ability) {
                    $ability = $this->tagServiceProvider->get($this->abilities, $ability);
                    if ($ability instanceof EntityAbilityInterface) {
                        if (!isset($data['entityTypes'][$ability->getEntityName()])) {
                            continue;
                        }
                        $data['entityTypes'][$ability->getEntityName()]['implemented'] = true;
                    }
                }

                return $data;
            } catch (ServiceClientRequestException $e) {
                $form->get('query')->addError(new FormError('Cannot connect to service'));
                throw new FormValidationException();
            }
        } catch (FormValidationException $e) {
            $errors = $this->vueViewCompiler->formErrorsVueViewCompile($form->getErrors(true));
            throw new AbilityException(['errors' => $errors]);
        }
    }

    public function isShowInMenu(): bool
    {
        return true;
    }
}
