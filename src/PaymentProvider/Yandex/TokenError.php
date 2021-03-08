<?php namespace App\PaymentProvider\Yandex;

class TokenError extends APIException
{
    public function __construct()
    {
        parent::__construct('Token is expired or incorrect', 401);
    }
}
