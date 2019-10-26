<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

interface FleetNfsItemInterface
{
    public function isVisisble(): bool;

    public function isHidden(): bool;

    public function getVisibleShips(): Collection;

    public function showManagement(): bool;

    public function getName(): string;

    public function getId(): int;

    public function getLeadShip(): ShipInterface;

    public function getUser(): UserInterface;
}
