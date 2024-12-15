<?php

namespace Stu\Orm\Entity;

use Stu\Component\Building\BuildingFunctionEnum;

interface ColonyShipQueueInterface
{
    public function getId(): int;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyShipQueueInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): ColonyShipQueueInterface;

    public function getRumpId(): int;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): ColonyShipQueueInterface;

    public function getFinishDate(): int;

    public function setFinishDate(int $finishDate): ColonyShipQueueInterface;

    public function getStopDate(): int;

    public function setStopDate(int $stopDate): ColonyShipQueueInterface;

    public function setBuildingFunction(BuildingFunctionEnum $buildingFunction): ColonyShipQueueInterface;

    public function getRump(): SpacecraftRumpInterface;

    public function setRump(SpacecraftRumpInterface $shipRump): ColonyShipQueueInterface;

    public function getSpacecraftBuildplan(): SpacecraftBuildplanInterface;

    public function setSpacecraftBuildplan(SpacecraftBuildplanInterface $spacecraftBuildplan): ColonyShipQueueInterface;

    public function getMode(): ?int;

    public function setMode(?int $mode): ColonyShipQueueInterface;

    public function getShip(): ?ShipInterface;

    public function setShip(?ShipInterface $ship): ColonyShipQueueInterface;
}
