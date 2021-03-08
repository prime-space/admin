<?php namespace App;

use Symfony\Component\Form\FormErrorIterator;

class VueViewCompiler
{
    const MOMENT_DATE_FORMAT = 'Y-m-d H:i';

    public function formErrorsVueViewCompile(FormErrorIterator $errors): array
    {
        $view = [];
        foreach ($errors as $error) {
            $view[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $view;
    }
}
