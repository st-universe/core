<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

interface FleetNfsItemInterface
{
    public function isHidden(): bool;

    public function getVisibleShips(): Collection;

    public function isFleetOfCurrentShip(): bool;

    public function showManagement(): bool;

    public function getName(): string;

    public function getId(): int;

    public function getLeadShip(): ShipInterface;

    public function getUser(): UserInterface;

    public function getDefendedColony(): ?ColonyInterface;

    public function getBlockedColony(): ?ColonyInterface;
}
