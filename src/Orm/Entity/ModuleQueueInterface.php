<?php

namespace Stu\Orm\Entity;

use Modules;

interface ModuleQueueInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function setColonyId(int $colonyId): ModuleQueueInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ModuleQueueInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): ModuleQueueInterface;

    public function getBuildingFunction(): int;

    public function setBuildingFunction(int $buildingFunction): ModuleQueueInterface;

    public function getModule(): Modules;
}