<?php

namespace Stu\Orm\Entity;

interface MiningQueueInterface
{
    public function getId(): int;

    public function getShipId(): int;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): MiningQueueInterface;
}
