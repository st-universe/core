<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Provides methods to check for buildings having certain functions
 *
 * @see BuildingEnum
 */
final class ColonyFunctionManager implements ColonyFunctionManagerInterface
{
    public const STATE_DISABLED = 0;
    public const STATE_ENABLED = 1;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    /** @var array<int, array<int, bool>> */
    private array $hasActiveBuildingByColonyAndFunction = [];

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function hasActiveFunction(
        PlanetFieldHostInterface $host,
        int $functionId,
        bool $useCache = true
    ): bool {
        if ($useCache === false) {
            return $this->hasBuildingWithFunction($host, $functionId, [self::STATE_ENABLED]);
        }
        return $this->hasActiveBuildingWithFunction($host, $functionId);
    }

    public function hasFunction(
        PlanetFieldHostInterface $host,
        int $functionId
    ): bool {
        return $this->hasBuildingWithFunction($host, $functionId, [self::STATE_DISABLED, self::STATE_ENABLED]);
    }

    public function getBuildingWithFunctionCount(
        PlanetFieldHostInterface $host,
        int $functionId,
        array $states
    ): int {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $host,
            [$functionId],
            $states
        );
    }

    /**
     * @param array<int> $states
     */
    private function hasBuildingWithFunction(PlanetFieldHostInterface $host, int $functionId, array $states): bool
    {
        return $this->getBuildingWithFunctionCount(
            $host,
            $functionId,
            $states
        ) > 0;
    }

    /**
     * Uses a very simple cache to avoid querying the same information over and over again
     */
    private function hasActiveBuildingWithFunction(PlanetFieldHostInterface $host, int $functionId): bool
    {
        $hostId = $host->getId();

        if (!isset($this->hasActiveBuildingByColonyAndFunction[$hostId])) {
            $this->hasActiveBuildingByColonyAndFunction[$hostId] = [];
        }
        if (!isset($this->hasActiveBuildingByColonyAndFunction[$hostId][$functionId])) {
            $this->hasActiveBuildingByColonyAndFunction[$hostId][$functionId] = $this->hasBuildingWithFunction($host, $functionId, [self::STATE_ENABLED]);
        }
        return $this->hasActiveBuildingByColonyAndFunction[$hostId][$functionId];
    }
}
