<?php

namespace Stu\Orm\Entity;

interface ShipCrewInterface
{
    public function getId(): int;

    public function getShidId(): int;

    public function setShipId(int $shipId): ShipCrewInterface;

    public function getCrewId(): int;

    public function setCrewId(int $crewId): ShipCrewInterface;

    public function getSlot(): int;

    public function setSlot(int $slot): ShipCrewInterface;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ShipCrewInterface;

    public function getCrew(): CrewInterface;

    public function setCrew(CrewInterface $crew): ShipCrewInterface;
}