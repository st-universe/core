<?php

namespace Stu\Module\Colony\Lib;

interface BuildingFunctionTalInterface
{
    public function isTorpedoFab(): bool;

    public function isAirfield(): bool;

    public function isFighterShipyard(): bool;

    public function isAcademy(): bool;

    public function isShipyard(): bool;

    public function getShipyardBuildingFunctionId(): ?int;

    public function isModuleFab(): bool;

    public function getModuleFabBuildingFunctionId(): ?int;
}