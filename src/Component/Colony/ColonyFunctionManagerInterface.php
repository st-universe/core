<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\ColonyInterface;

interface ColonyFunctionManagerInterface
{
    /**
     * Returns `true` if the colony hat at least one active building with the requested function id
     */
    public function hasActiveFunction(ColonyInterface $colony, int $functionId, bool $useCache = true): bool;

    /**
     * Returns `true` if the colony has at least on building with the requested function
     */
    public function hasFunction(
        ColonyInterface $colony,
        int $functionId
    ): bool;

    /**
     * Returns the count of buildings providing the requested function
     *
     * @param list<int> $states
     */
    public function getBuildingWithFunctionCount(
        ColonyInterface $colony,
        int $functionId,
        array $states
    ): int;
}
