<?php namespace App\AccessRule;

class RefundAccessRule implements AccessRuleInterface
{
    public function getId(): int
    {
        return 6;
    }

    public function getName(): string
    {
        return 'refundRule';
    }
}
