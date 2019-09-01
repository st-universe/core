<?php

namespace Stu\Orm\Entity;

use ModulesData;

interface BuildplanModuleInterface
{
    public function getId(): int;

    public function getBuildplanid(): int;

    public function setBuildplanId(int $buildplanId): BuildplanModuleInterface;

    public function getModuleType(): int;

    public function setModuleType(int $moduleType): BuildplanModuleInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): BuildplanModuleInterface;

    public function getModule(): ModulesData;
}