<?php namespace App\AccessRule;

use App\Entity\Service;
use Ewll\DBBundle\Repository\RepositoryProvider;

class ServiceAccessRule implements AccessRuleListInterface
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function getId(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return 'serviceRule';
    }

    public function isIdValid(int $id): bool
    {
        $service = $this->repositoryProvider->get(Service::class)->findById($id);

        return $service !== null;
    }
}
