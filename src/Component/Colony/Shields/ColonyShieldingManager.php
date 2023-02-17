<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Provides shielding related methods
 */
final class ColonyShieldingManager implements ColonyShieldingManagerInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private ColonyInterface $colony;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ColonyInterface $colony
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->colony = $colony;
    }

    public function updateActualShields(): void
    {
        $shieldState = false;
        $shields = 0;

        foreach ($this->colony->getPlanetFields() as $field) {
            $building = $field->getBuilding();

            if ($building === null || !$field->isActive()) {
                continue;
            }

            $functions = $building->getFunctions();

            if ($functions->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR)) {
                $shields += BuildingEnum::SHIELD_GENERATOR_CAPACITY;
                $shieldState = true;
            }

            if ($functions->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY)) {
                $shields += BuildingEnum::SHIELD_BATTERY_CAPACITY;
            }
        }

        if ($shieldState) {
            $this->colony->setShields(min($this->colony->getShields(), $shields));
        }
    }

    public function hasShielding(): bool
    {
        return $this->colonyFunctionManager->hasFunction(
            $this->colony,
            BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
        );
    }

    public function getMaxShielding(): int
    {
        return $this->planetFieldRepository->getMaxShieldsOfColony($this->colony->getId());
    }

    public function isShieldingEnabled(): bool
    {
        return $this->colonyFunctionManager->hasActiveFunction($this->colony, BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR)
            && $this->colony->getShields() > 0;
    }
}
