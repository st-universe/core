<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

use Override;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Provides shielding related methods
 */
final class ColonyShieldingManager implements ColonyShieldingManagerInterface
{
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private ColonyFunctionManagerInterface $colonyFunctionManager, private PlanetFieldHostInterface $host)
    {
    }

    #[Override]
    public function updateActualShields(): void
    {
        if (!$this->host instanceof ColonyInterface) {
            return;
        }

        $shieldState = false;
        $shields = 0;

        foreach ($this->host->getPlanetFields() as $field) {
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
            $this->host->setShields(min($this->host->getShields(), $shields));
        }
    }

    #[Override]
    public function hasShielding(): bool
    {
        return $this->colonyFunctionManager->hasFunction(
            $this->host,
            BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
        );
    }

    #[Override]
    public function getMaxShielding(): int
    {
        return $this->planetFieldRepository->getMaxShieldsOfHost($this->host);
    }

    #[Override]
    public function isShieldingEnabled(): bool
    {
        return $this->colonyFunctionManager->hasActiveFunction($this->host, BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR)
            && ($this->host instanceof ColonyInterface ? $this->host->getShields() > 0 : true);
    }
}
