<?php namespace App\Application\Ability;

interface AbilityInterface
{
    public function isShowInMenu(): bool;
}
