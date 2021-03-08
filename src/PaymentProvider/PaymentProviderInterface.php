<?php namespace App\PaymentProvider;

use App\Exception\CannotGetBallanceException;

interface PaymentProviderInterface
{
    public function getAccountNumber(array $config): string;
    /** @throws CannotGetBallanceException */
    public function getBalance(array $config): array;
}
