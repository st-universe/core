<?php

namespace Stu\Orm\Entity;

interface ShipRumpSpecialInterface
{
    public function getId(): int;

    public function getShipRumpId(): int;

    public function setShipRumpId(int $shipRumpId): ShipRumpSpecialInterface;

    public function getSpecialId(): int;

    public function setSpecialId(int $specialId): ShipRumpSpecialInterface;
}