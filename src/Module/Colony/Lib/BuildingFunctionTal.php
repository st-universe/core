<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingEnum;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Orm\Entity\BuildingFunctionInterface;

final class BuildingFunctionTal implements BuildingFunctionTalInterface
{
    /**
     * @var BuildingFunctionInterface[]
     */
    private array $buildingfunctionIds;

    public function __construct(
        array $buildingfunctionIds
    ) {
        $this->buildingfunctionIds = $buildingfunctionIds;
    }

    public function isTorpedoFab(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_TORPEDO_FAB);
    }

    public function isAirfield(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_AIRFIELD);
    }

    public function isFighterShipyard(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD);
    }

    public function isAcademy(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_ACADEMY);
    }

    public function isShipyard(): bool
    {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getShipyardOptions())) {
                return true;
            }
        }
        return false;
    }

    public function getShipyardBuildingFunctionId(): ?int {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getShipyardOptions())) {
                return $func->getId();
            }
        }
        return null;
    }

    public function isModuleFab(): bool
    {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getModuleFabOptions())) {
                return true;
            }
        }
        return false;
    }

    public function getModuleFabBuildingFunctionId(): ?int {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getModuleFabOptions())) {
                return $func->getId();
            }
        }
        return null;
    }

    private function hasFunction(int $functionId): bool
    {
        return array_key_exists($functionId, $this->buildingfunctionIds);
    }
}
