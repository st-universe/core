<?php

namespace Stu\Orm\Entity;

interface ShipyardShipQueueInterface
{
    public function getId(): int;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): ShipyardShipQueueInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): ShipyardShipQueueInterface;

    public function getRumpId(): int;

    public function setRumpId(int $shipRumpId): ShipyardShipQueueInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): ShipyardShipQueueInterface;

    public function getFinishDate(): int;

    public function setFinishDate(int $finishDate): ShipyardShipQueueInterface;

    public function getStopDate(): int;

    public function setStopDate(int $stopDate): ShipyardShipQueueInterface;

    public function getRump(): ShipRumpInterface;

    public function setRump(ShipRumpInterface $shipRump): ShipyardShipQueueInterface;

    public function getShipBuildplan(): ShipBuildplanInterface;

    public function setShipBuildplan(ShipBuildplanInterface $shipBuildplan): ShipyardShipQueueInterface;
}
