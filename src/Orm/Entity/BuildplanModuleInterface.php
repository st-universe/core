<?php

namespace Stu\Orm\Entity;

interface BuildplanModuleInterface
{
    public function getId(): int;

    public function getBuildplanid(): int;

    public function setBuildplanId(int $buildplanId): BuildplanModuleInterface;

    public function getModuleType(): int;

    public function setModuleType(int $moduleType): BuildplanModuleInterface;

    public function getModuleCount(): int;

    public function setModuleCount(int $moduleCount): BuildplanModuleInterface;

    public function getModuleSpecial(): ?int;

    public function setModuleSpecial(?int $moduleSpecial): BuildplanModuleInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): BuildplanModuleInterface;

    public function getModule(): ModuleInterface;

    public function setModule(ModuleInterface $module): BuildplanModuleInterface;
}
