<?php

namespace Stu\Orm\Entity;

interface ShipRumpModuleSpecialInterface
{
    public function getId(): int;

    public function setRumpId(int $rumpId): ShipRumpModuleSpecialInterface;

    public function getModuleSpecialId(): int;

    public function setModuleSpecialId(int $moduleSpecialId): ShipRumpModuleSpecialInterface;
}
