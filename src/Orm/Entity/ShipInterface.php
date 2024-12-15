<?php

namespace Stu\Orm\Entity;

interface ShipInterface extends SpacecraftInterface
{
    public function getFleetId(): ?int;

    public function setFleetId(?int $fleetId): ShipInterface;

    public function isUnderRetrofit(): bool;

    public function getIsFleetLeader(): bool;

    public function setIsFleetLeader(bool $isFleetLeader): ShipInterface;

    public function setFleet(?FleetInterface $fleet): ShipInterface;

    public function isFleetLeader(): bool;

    public function isBussardCollectorHealthy(): bool;

    public function isTractored(): bool;

    public function dockedOnTradePost(): bool;

    public function getTractoringSpacecraft(): ?SpacecraftInterface;

    public function setTractoringSpacecraft(?SpacecraftInterface $spacecraft): ShipInterface;

    public function getDockedTo(): ?StationInterface;

    public function setDockedTo(?StationInterface $dockedTo): ShipInterface;

    public function setDockedToId(?int $dockedToId): ShipInterface;

    public function canBuildConstruction(): bool;

    public function getMiningQueue(): ?MiningQueueInterface;

    public function getColonyShipQueue(): ?ColonyShipQueueInterface;

    public function setColonyShipQueue(?ColonyShipQueueInterface $queue): ShipInterface;

    public function getAstroState(): bool;
}
