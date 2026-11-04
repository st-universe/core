<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Repository\SpecialResearchDefinitionRepositoryInterface;

final class SpecialResearchDefinitionDeletionHandler
{
    public function __construct(private SpecialResearchDefinitionRepositoryInterface $repository) {}

    public function delete(SpecialResearchDefinition $definition): void
    {
        $this->repository->delete($definition);
    }
}
