<?php

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;

interface ModuleSpecialInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ModuleSpecialInterface;

    public function getSpecialId(): ModuleSpecialAbilityEnum;

    public function setSpecialId(ModuleSpecialAbilityEnum $specialId): ModuleSpecialInterface;

    public function getName(): string;
}
