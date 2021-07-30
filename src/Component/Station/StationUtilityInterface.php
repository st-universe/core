<?php

namespace Stu\Component\Station;

use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface StationUtilityInterface
{
    /**
     * @return ShipBuildplanInterface[]
     */
    public function getStationBuildplansByUser(int $userId): array;

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ShipBuildplanInterface;

    public function getDockedWorkbeeCount(ShipInterface $ship): int;

    public function hasEnoughDockedWorkbees(ShipInterface $ship, ShipRumpInterface $rump): bool;
}
