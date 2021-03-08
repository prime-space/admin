<?php namespace App\Application\Ability;

use App\Entity\Service;
use Symfony\Component\HttpFoundation\Request;

interface EntityAbilityInterface extends AbilityInterface
{
    public function pageMethod(Request $request, Service $service, int $entityId): array;

    public function getEntityName(): string;
}
