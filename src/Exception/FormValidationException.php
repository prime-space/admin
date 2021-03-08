<?php namespace App\Exception;

use Exception;

class FormValidationException extends Exception
{
    private $errors;

    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
