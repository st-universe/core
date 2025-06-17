<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Provides methods to check for buildings having certain functions
 *
 * @see BuildingEnum
 */
final class ColonyFunctionManager implements ColonyFunctionManagerInterface
{
    public const int STATE_DISABLED = 0;
    public const int STATE_ENABLED = 1;

    /** @var array<int, array<int, bool>> */
    private array $hasActiveBuildingByColonyAndFunction = [];

    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository) {}

    #[Override]
    public function hasActiveFunction(
        PlanetFieldHostInterface $host,
        BuildingFunctionEnum $function,
        bool $useCache = true,
        array $ignoredFieldIds = []
    ): bool {

        return $useCache
            ? $this->hasActiveBuildingWithFunction($host, $function, $ignoredFieldIds)
            : $this->hasBuildingWithFunction($host, $function, [self::STATE_ENABLED], $ignoredFieldIds);
    }

    #[Override]
    public function hasFunction(
        PlanetFieldHostInterface $host,
        BuildingFunctionEnum $function
    ): bool {
        return $this->hasBuildingWithFunction($host, $function, [self::STATE_DISABLED, self::STATE_ENABLED]);
    }

    #[Override]
    public function getBuildingWithFunctionCount(
        PlanetFieldHostInterface $host,
        BuildingFunctionEnum $function,
        array $states,
        array $ignoredFieldIds = []
    ): int {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $host,
            [$function],
            $states,
            $ignoredFieldIds
        );
    }

    /**
     * @param array<int> $states
     * @param array<int> $ignoredFieldIds
     */
    private function hasBuildingWithFunction(
        PlanetFieldHostInterface $host,
        BuildingFunctionEnum $function,
        array $states,
        array $ignoredFieldIds = []
    ): bool {
        return $this->getBuildingWithFunctionCount(
            $host,
            $function,
            $states,
            $ignoredFieldIds
        ) > 0;
    }

    /**
     * Uses a very simple cache to avoid querying the same information over and over again
     * 
     * @param array<int> $ignoredFieldIds
     */
    private function hasActiveBuildingWithFunction(PlanetFieldHostInterface $host, BuildingFunctionEnum $function, array $ignoredFieldIds): bool
    {
        $hostId = $host->getId();
        $value = $function->value;

        if (!isset($this->hasActiveBuildingByColonyAndFunction[$hostId])) {
            $this->hasActiveBuildingByColonyAndFunction[$hostId] = [];
        }
        if (!isset($this->hasActiveBuildingByColonyAndFunction[$hostId][$value])) {
            $this->hasActiveBuildingByColonyAndFunction[$hostId][$value] = $this->hasBuildingWithFunction($host, $function, [self::STATE_ENABLED], $ignoredFieldIds);
        }
        return $this->hasActiveBuildingByColonyAndFunction[$hostId][$value];
    }
}
