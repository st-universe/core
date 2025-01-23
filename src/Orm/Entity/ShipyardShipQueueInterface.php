<?php

namespace Stu\Orm\Entity;

interface ShipyardShipQueueInterface
{
    public function getId(): int;

    public function getStation(): StationInterface;

    public function setStation(StationInterface $station): ShipyardShipQueueInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): ShipyardShipQueueInterface;

    public function getRumpId(): int;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): ShipyardShipQueueInterface;

    public function getFinishDate(): int;

    public function setFinishDate(int $finishDate): ShipyardShipQueueInterface;

    public function getStopDate(): int;

    public function setStopDate(int $stopDate): ShipyardShipQueueInterface;

    public function getRump(): SpacecraftRumpInterface;

    public function setRump(SpacecraftRumpInterface $shipRump): ShipyardShipQueueInterface;

    public function getSpacecraftBuildplan(): SpacecraftBuildplanInterface;

    public function setSpacecraftBuildplan(SpacecraftBuildplanInterface $spacecraftBuildplan): ShipyardShipQueueInterface;
}
