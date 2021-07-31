<?php

namespace Stu\Orm\Entity;

interface ConstructionProgressModuleInterface
{
    public function getId(): int;

    public function getConstructionProgress(): ConstructionProgressInterface;

    public function setConstructionProgress(ConstructionProgressInterface $progress): ConstructionProgressModuleInterface;

    public function getModule(): ModuleInterface;

    public function setModule(ModuleInterface $module): ConstructionProgressModuleInterface;
}
