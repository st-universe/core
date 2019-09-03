<?php

namespace Stu\Orm\Entity;

interface ModuleSpecialInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ModuleSpecialInterface;

    public function getSpecialId(): int;

    public function setSpecialId(int $specialId): ModuleSpecialInterface;

    public function getName(): string;
}