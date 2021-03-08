<?php namespace App\Controller;

use App\Authenticator;
use App\Exception\ActionErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthController extends Controller
{
    private $authenticator;

    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function signInPage(Request $request)
    {
        return $this->render('signIn.html.twig');
    }

    public function signIn(Request $request)
    {
        $form = $this->createFormBuilder(null, ['attr' => ['@submit.prevent' => 'submit']])
            ->setAction($this->generateUrl('signIn'))
            ->add('login', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('password', PasswordType::class, ['constraints' => [new NotBlank()]])
            ->add('save', SubmitType::class, ['label' => 'Sign in', 'attr' => [':disabled' => 'submitted']])
            ->getForm();

        $form->handleRequest($request);

        try {
            if (!$form->isSubmitted()) {
                throw new ActionErrorException(400);
            }
            if (!$form->isValid()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = "{$error->getOrigin()->getName()}: {$error->getMessage()}";
                }
                throw new ActionErrorException(400, $errors);
            }

            $data = $form->getData();

            $isSigned = $this->authenticator->signIn($data['login'], $data['password']);

            if (!$isSigned) {
                throw new ActionErrorException(400, ['Incorrect login or password']);
            }

            return new JsonResponse();
        } catch (ActionErrorException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }
    }

    public function signOut()
    {
        $this->authenticator->signOut();

        return $this->redirect('/signIn');
    }
}
