<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\ColonyInterface;

interface ColonyFunctionManagerInterface
{
    /**
     * Returns `true` if the colony hat at least one active building with the requested function id
     */
    public function hasActiveFunction(ColonyInterface $colony, int $buildingFunctionId): bool;
}
