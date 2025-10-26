<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Provides shielding related methods
 */
final class ColonyShieldingManager implements ColonyShieldingManagerInterface
{
    public const int SHIELD_GENERATOR_CAPACITY = 4000;
    public const int SHIELD_BATTERY_CAPACITY = 10000;

    public function __construct(
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private PlanetFieldHostInterface $host
    ) {}

    #[\Override]
    public function updateActualShields(): void
    {
        if (!$this->host instanceof Colony) {
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

            if ($functions->containsKey(BuildingFunctionEnum::SHIELD_GENERATOR->value)) {
                $shields += self::SHIELD_GENERATOR_CAPACITY;
                $shieldState = true;
            }

            if ($functions->containsKey(BuildingFunctionEnum::SHIELD_BATTERY->value)) {
                $shields += self::SHIELD_BATTERY_CAPACITY;
            }
        }

        if ($shieldState) {
            $changeable = $this->host->getChangeable();
            $changeable->setShields(min($changeable->getShields(), $shields));
        }
    }

    #[\Override]
    public function hasShielding(): bool
    {
        return $this->colonyFunctionManager->hasFunction(
            $this->host,
            BuildingFunctionEnum::SHIELD_GENERATOR
        );
    }

    #[\Override]
    public function getMaxShielding(): int
    {
        return $this->planetFieldRepository->getMaxShieldsOfHost($this->host);
    }

    #[\Override]
    public function isShieldingEnabled(): bool
    {
        return $this->colonyFunctionManager->hasActiveFunction($this->host, BuildingFunctionEnum::SHIELD_GENERATOR)
            && ($this->host instanceof Colony ? $this->host->getChangeable()->getShields() > 0 : true);
    }
}
