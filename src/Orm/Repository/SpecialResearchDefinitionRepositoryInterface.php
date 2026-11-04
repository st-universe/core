<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\SpecialResearchDefinition;

interface SpecialResearchDefinitionRepositoryInterface
{
    /** @return list<SpecialResearchDefinition> */
    public function getByFaction(int $factionId): array;

    public function delete(SpecialResearchDefinition $definition): void;
}
