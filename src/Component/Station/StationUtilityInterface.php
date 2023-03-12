<?php

namespace Stu\Component\Station;

use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface StationUtilityInterface
{
    /**
     * @return ShipBuildplanInterface[]
     */
    public function getStationBuildplansByUser(int $userId): array;

    /**
     * @return ShipBuildplanInterface[]
     */
    public function getShipyardBuildplansByUser(int $userId): array;

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?ShipBuildplanInterface;

    public function getDockedWorkbeeCount(ShipInterface $ship): int;

    public function hasEnoughDockedWorkbees(ShipInterface $station, ShipRumpInterface $rump): bool;

    public function getConstructionProgress(ShipInterface $ship): ?ConstructionProgressInterface;

    public function reduceRemainingTicks(ConstructionProgressInterface $progress): void;

    public function finishStation(ShipInterface $ship, ConstructionProgressInterface $progress): void;

    public function finishScrapping(ShipInterface $station, ConstructionProgressInterface $progress): void;

    public function canManageShips(ShipInterface $ship): bool;

    public function canRepairShips(ShipInterface $ship): bool;
}
