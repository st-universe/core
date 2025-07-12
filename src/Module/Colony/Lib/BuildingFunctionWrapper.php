<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\BuildingFunction;

final class BuildingFunctionWrapper implements BuildingFunctionWrapperInterface
{
    /** @param array<BuildingFunction> $buildingfunctions */
    public function __construct(private array $buildingfunctions) {}

    #[Override]
    public function isTorpedoFab(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::TORPEDO_FAB);
    }

    #[Override]
    public function isAirfield(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::AIRFIELD);
    }

    #[Override]
    public function isFighterShipyard(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::FIGHTER_SHIPYARD);
    }

    #[Override]
    public function isAcademy(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::ACADEMY);
    }

    #[Override]
    public function isFabHall(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::FABRICATION_HALL);
    }

    #[Override]
    public function isTechCenter(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::TECH_CENTER);
    }

    #[Override]
    public function isShipyard(): bool
    {
        return array_any(
            $this->buildingfunctions,
            fn(BuildingFunction $function): bool => $function->getFunction()->isShipyard()
        );
    }

    #[Override]
    public function getShipyardBuildingFunctionId(): ?int
    {
        return array_find(
            $this->buildingfunctions,
            fn(BuildingFunction $function): bool => $function->getFunction()->isShipyard()
        )?->getId();
    }

    #[Override]
    public function isModuleFab(): bool
    {
        return array_any(
            $this->buildingfunctions,
            fn(BuildingFunction $function): bool => $function->getFunction()->isModuleFab()
        );
    }

    #[Override]
    public function isWarehouse(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::WAREHOUSE);
    }

    #[Override]
    public function isSubspaceTelescope(): bool
    {
        return $this->hasFunction(BuildingFunctionEnum::SUBSPACE_TELESCOPE);
    }

    #[Override]
    public function getModuleFabBuildingFunctionId(): ?int
    {
        return array_find(
            $this->buildingfunctions,
            fn(BuildingFunction $function): bool => $function->getFunction()->isModuleFab()
        )?->getId();
    }

    #[Override]
    public function getFabHallBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctions as $func) {
            if ($func->getFunction() === BuildingFunctionEnum::FABRICATION_HALL) {
                return $func->getId();
            }
        }
        return null;
    }

    #[Override]
    public function getTechCenterBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctions as $func) {
            if ($func->getFunction() === BuildingFunctionEnum::TECH_CENTER) {
                return $func->getId();
            }
        }
        return null;
    }

    #[Override]
    public function getSubspaceTelescopeBuildingFunctionId(): ?int
    {
        foreach ($this->buildingfunctions as $func) {
            if ($func->getFunction() === BuildingFunctionEnum::SUBSPACE_TELESCOPE) {
                return $func->getId();
            }
        }
        return null;
    }

    private function hasFunction(BuildingFunctionEnum $function): bool
    {
        return array_key_exists($function->value, $this->buildingfunctions);
    }
}
