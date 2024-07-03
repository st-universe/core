<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Component\Building\BuildingEnum;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Orm\Entity\BuildingFunctionInterface;

final class BuildingFunctionTal implements BuildingFunctionTalInterface
{
    public function __construct(
        /**
         * @var BuildingFunctionInterface[]
         */
        private array $buildingfunctionIds
    )
    {
    }

    #[Override]
    public function isTorpedoFab(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_TORPEDO_FAB);
    }

    #[Override]
    public function isAirfield(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_AIRFIELD);
    }

    #[Override]
    public function isFighterShipyard(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD);
    }

    #[Override]
    public function isAcademy(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_ACADEMY);
    }

    #[Override]
    public function isFabHall(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_FABRICATION_HALL);
    }

    #[Override]
    public function isTechCenter(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_TECH_CENTER);
    }

    #[Override]
    public function isShipyard(): bool
    {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getShipyardOptions())) {
                return true;
            }
        }
        return false;
    }

    #[Override]
    public function getShipyardBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getShipyardOptions())) {
                return $func->getId();
            }
        }
        return null;
    }

    #[Override]
    public function isModuleFab(): bool
    {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getModuleFabOptions())) {
                return true;
            }
        }
        return false;
    }

    #[Override]
    public function isWarehouse(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_WAREHOUSE);
    }

    #[Override]
    public function isSubspaceTelescope(): bool
    {
        return $this->hasFunction(BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE);
    }

    #[Override]
    public function getModuleFabBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctionIds as $func) {
            if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getModuleFabOptions())) {
                return $func->getId();
            }
        }
        return null;
    }

    #[Override]
    public function getFabHallBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctionIds as $func) {
            if ($func->getFunction() === BuildingEnum::BUILDING_FUNCTION_FABRICATION_HALL) {
                return $func->getId();
            }
        }
        return null;
    }

    #[Override]
    public function getTechCenterBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctionIds as $func) {
            if ($func->getFunction() === BuildingEnum::BUILDING_FUNCTION_TECH_CENTER) {
                return $func->getId();
            }
        }
        return null;
    }

    #[Override]
    public function getSubspaceTelescopeBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctionIds as $func) {
            if ($func->getFunction() === BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE) {
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
