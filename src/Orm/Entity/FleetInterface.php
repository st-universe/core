<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface FleetInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): FleetInterface;

    public function getUserId(): int;

    /**
     * @return ShipInterface[]
     */
    public function getShips(): iterable;

    public function getShipCount(): int;

    public function ownedByCurrentUser(): bool;

    public function getLeadShip(): ShipInterface;

    public function setLeadShip(ShipInterface $ship): FleetInterface;

    public function getAvailableShips(): iterable;

    public function deactivateSystem(int $system): void;

    public function activateSystem(int $system): void;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): FleetInterface;

    public function getPointSum(): int;
}