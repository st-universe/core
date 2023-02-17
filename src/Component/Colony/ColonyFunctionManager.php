<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Component\Building\BuildingEnum;
use Stu\Orm\Entity\ColonyInterface;
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
        ColonyInterface $colony,
        int $functionId,
        bool $useCache = true
    ): bool {
        if ($useCache === false) {
            return $this->hasBuildingWithFunction($colony, $functionId, [self::STATE_ENABLED]);
        }
        return $this->hasActiveBuildingWithFunction($colony, $functionId);
    }

    public function hasFunction(
        ColonyInterface $colony,
        int $functionId
    ): bool {
        return $this->hasBuildingWithFunction($colony, $functionId, [self::STATE_DISABLED, self::STATE_ENABLED]);
    }

    public function getBuildingWithFunctionCount(
        ColonyInterface $colony,
        int $functionId,
        array $states
    ): int {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony->getId(),
            [$functionId],
            $states
        );
    }

    /**
     * @param list<int> $states
     */
    private function hasBuildingWithFunction(ColonyInterface $colony, int $functionId, array $states): bool
    {
        return $this->getBuildingWithFunctionCount(
            $colony,
            $functionId,
            $states
        ) > 0;
    }

    /**
     * Uses a very simple cache to avoid querying the same information over and over again
     */
    private function hasActiveBuildingWithFunction(ColonyInterface $colony, int $functionId): bool
    {
        $colonyId = $colony->getId();

        if (!isset($this->hasActiveBuildingByColonyAndFunction[$colonyId])) {
            $this->hasActiveBuildingByColonyAndFunction[$colonyId] = [];
        }
        if (!isset($this->hasActiveBuildingByColonyAndFunction[$colonyId][$functionId])) {
            $this->hasActiveBuildingByColonyAndFunction[$colonyId][$functionId] = $this->hasBuildingWithFunction($colony, $functionId, [self::STATE_ENABLED]);
        }
        return $this->hasActiveBuildingByColonyAndFunction[$colonyId][$functionId];
    }
}
