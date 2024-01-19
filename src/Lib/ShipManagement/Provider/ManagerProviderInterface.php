<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;

interface ManagerProviderInterface
{
    public function getUser(): UserInterface;

    public function getEps(): int;

    public function lowerEps(int $amount): ManagerProviderInterface;

    public function getName(): string;

    public function getSectorString(): string;

    public function getFreeCrewAmount(): int;

    public function addShipCrew(ShipInterface $ship, int $amount): void;

    public function getFreeCrewStorage(): int;

    /**
     * @param array<ShipCrewInterface> $crewAssignments
     */
    public function addCrewAssignments(array $crewAssignments): void;

    /**
     * @return Collection<int, StorageInterface>
     */
    public function getStorage(): Collection;

    public function upperStorage(CommodityInterface $commodity, int $amount): void;

    public function lowerStorage(CommodityInterface $commodity, int $amount): void;
}
