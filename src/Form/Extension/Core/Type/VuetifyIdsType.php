<?php namespace App\Form\Extension\Core\Type;

use App\Form\Extension\Core\DataTransformer\VuetifyIdsTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class VuetifyIdsType extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new VuetifyIdsTransformer());
    }
}
