<?php

namespace Stu\Orm\Entity;

interface StationShipRepairInterface
{
    public function getId(): int;

    public function getStationId(): int;

    public function getShipId(): int;

    public function getStation(): StationInterface;

    public function setStation(StationInterface $station): StationShipRepairInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): StationShipRepairInterface;
}
