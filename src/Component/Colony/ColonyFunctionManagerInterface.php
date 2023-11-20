<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Lib\Colony\PlanetFieldHostInterface;

interface ColonyFunctionManagerInterface
{
    /**
     * Returns `true` if the colony hat at least one active building with the requested function id
     */
    public function hasActiveFunction(PlanetFieldHostInterface $host, int $functionId, bool $useCache = true): bool;

    /**
     * Returns `true` if the colony has at least on building with the requested function
     */
    public function hasFunction(
        PlanetFieldHostInterface $host,
        int $functionId
    ): bool;

    /**
     * Returns the count of buildings providing the requested function
     *
     * @param array<int> $states
     */
    public function getBuildingWithFunctionCount(
        PlanetFieldHostInterface $host,
        int $functionId,
        array $states
    ): int;
}
