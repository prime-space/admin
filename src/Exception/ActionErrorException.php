<?php namespace App\Exception;

use Exception;

class ActionErrorException extends Exception
{
    private $errors;

    public function __construct($code, $errors = [])
    {
        parent::__construct('', $code);

        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
