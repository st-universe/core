<?php

namespace Stu\Component\Station;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;

interface StationUtilityInterface
{
    /**
     * @return SpacecraftBuildplan[]
     */
    public function getStationBuildplansByUser(int $userId): array;

    /**
     * @return SpacecraftBuildplan[]
     */
    public function getShipyardBuildplansByUser(int $userId): array;

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?SpacecraftBuildplan;

    public function getDockedWorkbeeCount(Station $station): int;

    public function getNeededWorkbeeCount(Station $station, SpacecraftRump $rump): int;

    public function hasEnoughDockedWorkbees(Station $station, SpacecraftRump $rump): bool;

    public function reduceRemainingTicks(ConstructionProgress $progress): void;

    public function finishStation(ConstructionProgress $progress): void;

    public function finishScrapping(ConstructionProgress $progress, InformationInterface $information): void;

    public function canManageShips(Station $station): bool;

    public function canRepairShips(Station $station): bool;
}
