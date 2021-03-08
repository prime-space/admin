<?php namespace App\AccessRule;

class TicketsAccessRule implements AccessRuleInterface
{
    public function getId(): int
    {
        return 2;
    }

    public function getName(): string
    {
        return 'ticketsRule';
    }
}
