<?php

namespace Stu\Orm\Entity;

use Crew;

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

    public function setUserId(int $userId): ShipCrewInterface;

    public function getCrew(): Crew;
}