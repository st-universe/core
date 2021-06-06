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
    public function getShips(): Collection;

    public function getShipCount(): int;

    public function getLeadShip(): ShipInterface;

    public function setLeadShip(ShipInterface $ship): FleetInterface;

    public function getAvailableShips(): iterable;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): FleetInterface;

    public function getDefendedColony(): ?ColonyInterface;

    public function setDefendedColony(?ColonyInterface $defendedColony): FleetInterface;

    public function getBlockedColony(): ?ColonyInterface;

    public function setBlockedColony(?ColonyInterface $blockedColony): FleetInterface;

    public function getCrewSum(): int;
}
