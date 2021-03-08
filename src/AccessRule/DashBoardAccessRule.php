<?php namespace App\AccessRule;

class DashBoardAccessRule implements AccessRuleInterface
{
    public function getId(): int
    {
        return 1;
    }

    public function getName(): string
    {
        return 'dashBoardRule';
    }
}
