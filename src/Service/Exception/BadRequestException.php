<?php namespace App\Service\Exception;

use Exception;

class BadRequestException extends Exception
{
    private $errors;

    public function __construct(array $errors, Exception $previous)
    {
        $this->errors = $errors;
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
