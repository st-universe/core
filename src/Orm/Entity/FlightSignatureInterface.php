<?php

namespace Stu\Orm\Entity;

interface FlightSignatureInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): FlightSignatureInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): FlightSignatureInterface;

    public function getTime(): int;

    public function setTime(int $time): FlightSignatureInterface;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): FlightSignatureInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): FlightSignatureInterface;
}
