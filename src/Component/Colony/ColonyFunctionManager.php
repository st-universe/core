<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\ColonyInterface;

/**
 * Provides methods to check for buildings having certain functions
 */
final class ColonyFunctionManager implements ColonyFunctionManagerInterface
{
    public function hasActiveFunction(
        ColonyInterface $colony,
        int $buildingFunctionId
    ): bool {
        return $colony->hasActiveBuildingWithFunction($buildingFunctionId);
    }
}
