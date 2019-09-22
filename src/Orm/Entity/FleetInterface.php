<?php

namespace Stu\Orm\Entity;

use Ship;

interface FleetInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): FleetInterface;

    public function getUserId(): int;

    public function getFleetLeader(): int;

    public function setFleetLeader(int $leaderShipId): FleetInterface;

    public function getShips(): iterable;

    public function getShipCount(): int;

    public function ownedByCurrentUser(): bool;

    public function getLeadShip(): Ship;

    public function getAvailableShips(): iterable;

    public function autochangeLeader(Ship $obj): void;

    public function deactivateSystem(int $system): void;

    public function activateSystem(int $system): void;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): FleetInterface;

    public function getPointSum(): int;
}