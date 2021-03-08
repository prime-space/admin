<?php namespace App\PaymentProvider\Yandex;

class FormatError extends APIException
{
    public function __construct()
    {
        parent::__construct('Request is missformated', 400);
    }
}
