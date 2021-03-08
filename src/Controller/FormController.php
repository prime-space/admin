<?php namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class FormController extends Controller
{
    public function view()
    {
        //@TODO hardcode
        $paymentSystemsChoice = [
            'yandex' => 1,
        ];
        $servicesChoice = [
            'primearea.biz' => 1,
            'payarea24.com' => 2,
        ];
        $form = $this->createFormBuilder(null, ['attr' => ['@submit.prevent' => 'createAccount']])
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
            ->add('shop', CheckboxType::class, ['required' => false])
            ->add('merchant', CheckboxType::class, ['required' => false])
            ->add('withdraw', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => [':disabled' => 'createAccountSubmitted']])
            ->getForm();

        return $this->render('form.html.twig', ['form' => $form->createView()]);
    }
}
