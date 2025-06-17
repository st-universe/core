<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;

interface ColonyFunctionManagerInterface
{
    /**
     * Returns `true` if the colony hat at least one active building with the requested function id
     * 
     * @param array<int> $ignoredFieldIds
     */
    public function hasActiveFunction(PlanetFieldHostInterface $host, BuildingFunctionEnum $function, bool $useCache = true, array $ignoredFieldIds = []): bool;

    /**
     * Returns `true` if the colony has at least on building with the requested function
     */
    public function hasFunction(
        PlanetFieldHostInterface $host,
        BuildingFunctionEnum $function
    ): bool;

    /**
     * Returns the count of buildings providing the requested function
     *
     * @param array<int> $states
     * @param array<int> $ignoredFieldIds
     */
    public function getBuildingWithFunctionCount(
        PlanetFieldHostInterface $host,
        BuildingFunctionEnum $function,
        array $states,
        array $ignoredFieldIds = []
    ): int;
}
