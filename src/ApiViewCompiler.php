<?php namespace App;

class ApiViewCompiler
{
    public function compileErrorView(string $errorText, string $formElement = 'form')
    {
        return [
            $formElement => $errorText,
        ];
    }
}
