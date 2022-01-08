<?php

namespace Stu\Orm\Entity;

interface FlightSignatureInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): FlightSignatureInterface;

    public function getShipId(): int;

    public function setShipId(int $shipId): FlightSignatureInterface;

    public function getShipName(): string;

    public function setShipName(string $name): FlightSignatureInterface;

    public function isCloaked(): bool;

    public function setIsCloaked(bool $isCloaked): FlightSignatureInterface;

    public function getRump(): ShipRumpInterface;

    public function setRump(ShipRumpInterface $shipRump): FlightSignatureInterface;

    public function getTime(): int;

    public function setTime(int $time): FlightSignatureInterface;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): FlightSignatureInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): FlightSignatureInterface;

    public function getFromDirection(): int;

    public function setFromDirection(int $direction): FlightSignatureInterface;

    public function getToDirection(): int;

    public function setToDirection(int $direction): FlightSignatureInterface;
}
