<?php

namespace Stu\Orm\Entity;

use Stu\Component\Ship\ShipModuleTypeEnum;

interface BuildplanModuleInterface
{
    public function getId(): int;

    public function getBuildplan(): ShipBuildplanInterface;

    public function setBuildplan(ShipBuildplanInterface $buildplan): BuildplanModuleInterface;

    public function getModuleType(): ShipModuleTypeEnum;

    public function setModuleType(ShipModuleTypeEnum $type): BuildplanModuleInterface;

    public function getModuleCount(): int;

    public function setModuleCount(int $moduleCount): BuildplanModuleInterface;

    public function getModuleSpecial(): ?int;

    public function setModuleSpecial(?int $moduleSpecial): BuildplanModuleInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): BuildplanModuleInterface;

    public function getModule(): ModuleInterface;

    public function setModule(ModuleInterface $module): BuildplanModuleInterface;
}
