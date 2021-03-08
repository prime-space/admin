<?php namespace App\Exception;

use Exception;

class ServiceClientRequestException extends Exception
{
    public function __construct(Exception $previous = null)
    {
        $message = null !== $previous ? $previous->getMessage() : '';
        $code = null !== $previous ? $previous->getCode() : 0;
        parent::__construct($message, $code, $previous);
    }
}
