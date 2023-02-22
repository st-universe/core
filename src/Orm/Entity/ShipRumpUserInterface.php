<?php

namespace Stu\Orm\Entity;

interface ShipRumpUserInterface
{
    public function getId(): int;

    public function getShipRumpId(): int;

    public function setShipRumpId(int $shipRumpId): ShipRumpUserInterface;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ShipRumpUserInterface;
}
