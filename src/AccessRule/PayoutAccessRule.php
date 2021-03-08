<?php namespace App\AccessRule;

class PayoutAccessRule implements AccessRuleInterface
{
    public function getId(): int
    {
        return 4;
    }

    public function getName(): string
    {
        return 'payoutRule';
    }
}
