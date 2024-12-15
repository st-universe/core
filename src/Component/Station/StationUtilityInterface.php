<?php

namespace Stu\Component\Station;

use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\StationInterface;

interface StationUtilityInterface
{
    /**
     * @return SpacecraftBuildplanInterface[]
     */
    public function getStationBuildplansByUser(int $userId): array;

    /**
     * @return SpacecraftBuildplanInterface[]
     */
    public function getShipyardBuildplansByUser(int $userId): array;

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?SpacecraftBuildplanInterface;

    public function getDockedWorkbeeCount(StationInterface $station): int;

    public function getNeededWorkbeeCount(StationInterface $station, SpacecraftRumpInterface $rump): int;

    public function hasEnoughDockedWorkbees(StationInterface $station, SpacecraftRumpInterface $rump): bool;

    public function reduceRemainingTicks(ConstructionProgressInterface $progress): void;

    public function finishStation(ConstructionProgressInterface $progress): void;

    public function finishScrapping(ConstructionProgressInterface $progress): void;

    public function canManageShips(StationInterface $station): bool;

    public function canRepairShips(StationInterface $station): bool;
}
