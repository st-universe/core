<?php

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;

interface BuildplanModuleInterface
{
    public function getId(): int;

    public function getBuildplan(): SpacecraftBuildplanInterface;

    public function setBuildplan(SpacecraftBuildplanInterface $buildplan): BuildplanModuleInterface;

    public function getModuleType(): SpacecraftModuleTypeEnum;

    public function setModuleType(SpacecraftModuleTypeEnum $type): BuildplanModuleInterface;

    public function getModuleCount(): int;

    public function setModuleCount(int $moduleCount): BuildplanModuleInterface;

    public function getModuleSpecial(): ?int;

    public function setModuleSpecial(?int $moduleSpecial): BuildplanModuleInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): BuildplanModuleInterface;

    public function getModule(): ModuleInterface;

    public function setModule(ModuleInterface $module): BuildplanModuleInterface;
}
