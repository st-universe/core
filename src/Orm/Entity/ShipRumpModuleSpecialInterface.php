<?php

namespace Stu\Orm\Entity;

interface ShipRumpModuleSpecialInterface
{
    public function getId(): int;

    public function getShipRumpId(): int;

    public function setShipRumpId(int $shipRumpId): ShipRumpModuleSpecialInterface;

    public function getModuleSpecialId(): int;

    public function setModuleSpecialId(int $moduleSpecialId): ShipRumpModuleSpecialInterface;
}