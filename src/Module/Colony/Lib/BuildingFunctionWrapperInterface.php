<?php

namespace Stu\Module\Colony\Lib;

interface BuildingFunctionWrapperInterface
{
    public function isTorpedoFab(): bool;

    public function isAirfield(): bool;

    public function isFighterShipyard(): bool;

    public function isAcademy(): bool;

    public function isShipyard(): bool;

    public function isModuleFab(): bool;

    public function isWarehouse(): bool;

    public function isFabHall(): bool;

    public function isTechCenter(): bool;

    public function isSubspaceTelescope(): bool;

    public function getShipyardBuildingFunctionId(): ?int;

    public function getModuleFabBuildingFunctionId(): ?int;

    public function getFabHallBuildingFunctionId(): ?int;

    public function getTechCenterBuildingFunctionId(): ?int;

    public function getSubspaceTelescopeBuildingFunctionId(): ?int;
}
