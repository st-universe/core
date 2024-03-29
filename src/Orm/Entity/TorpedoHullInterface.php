<?php

namespace Stu\Orm\Entity;

interface TorpedoHullInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): TorpedoHullInterface;

    public function getTorpedoType(): int;

    public function setTorpedoType(int $torpedoType): TorpedoHullInterface;

    public function getModificator(): int;

    public function setModificator(int $Modificator): TorpedoHullInterface;

    public function getTorpedo(): TorpedoTypeInterface;

    public function getModule(): ModuleInterface;
}
