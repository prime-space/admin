<?php namespace App\AccessRule;

interface AccessRuleInterface
{
    public function getId(): int;
    public function getName(): string;
}
