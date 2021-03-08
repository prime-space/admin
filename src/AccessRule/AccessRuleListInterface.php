<?php namespace App\AccessRule;

interface AccessRuleListInterface extends AccessRuleInterface
{
    public function isIdValid(int $id): bool;
}
