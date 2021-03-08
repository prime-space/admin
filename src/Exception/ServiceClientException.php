<?php namespace App\Exception;

use Exception;

class ServiceClientException extends Exception
{
    public function __construct($code)
    {
        parent::__construct('', $code);
    }
}
