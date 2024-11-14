<?php

namespace Stu\Orm\Entity;

use Stu\Component\Building\BuildingFunctionEnum;

interface ModuleQueueInterface
{
    public function getId(): int;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ModuleQueueInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ModuleQueueInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): ModuleQueueInterface;

    public function getBuildingFunction(): BuildingFunctionEnum;

    public function setBuildingFunction(BuildingFunctionEnum $buildingFunction): ModuleQueueInterface;

    public function getModule(): ModuleInterface;

    public function setModule(ModuleInterface $module): ModuleQueueInterface;
}
