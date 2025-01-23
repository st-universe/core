<?php

namespace Stu\Orm\Entity;

use Stu\Component\Map\DirectionEnum;

interface FlightSignatureInterface
{
    public function getId(): int;

    public function setUserId(int $userId): FlightSignatureInterface;

    public function getShipId(): int;

    public function setShipId(int $shipId): FlightSignatureInterface;

    public function getShipName(): string;

    public function setSpacecraftName(string $name): FlightSignatureInterface;

    public function isCloaked(): bool;

    public function setIsCloaked(bool $isCloaked): FlightSignatureInterface;

    public function getRump(): SpacecraftRumpInterface;

    public function setRump(SpacecraftRumpInterface $shipRump): FlightSignatureInterface;

    public function getTime(): int;

    public function setTime(int $time): FlightSignatureInterface;

    public function getLocation(): LocationInterface;

    public function setLocation(LocationInterface $location): FlightSignatureInterface;

    public function getFromDirection(): ?DirectionEnum;

    public function setFromDirection(DirectionEnum $direction): FlightSignatureInterface;

    public function getToDirection(): ?DirectionEnum;

    public function setToDirection(DirectionEnum $direction): FlightSignatureInterface;
}
