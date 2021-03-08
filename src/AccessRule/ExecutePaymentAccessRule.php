<?php namespace App\AccessRule;

class ExecutePaymentAccessRule implements AccessRuleInterface
{
    public function getId(): int
    {
        return 5;
    }

    public function getName(): string
    {
        return 'executePaymentRule';
    }
}
