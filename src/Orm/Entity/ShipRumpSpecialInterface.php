<?php

namespace Stu\Orm\Entity;

interface ShipRumpSpecialInterface
{
    public function getId(): int;

    public function setRumpId(int $rumpId): ShipRumpSpecialInterface;

    public function getSpecialId(): int;

    public function setSpecialId(int $specialId): ShipRumpSpecialInterface;
}
