<?php

namespace Stu\Component\Spacecraft\Repair;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Spacecraft;

interface RepairUtilInterface
{
    /**
     * @return array<int, int>
     */
    public function determineSpareParts(SpacecraftWrapperInterface $wrapper, bool $tickBased): array;

    /** @param array<int, int> $neededParts */
    public function enoughSparePartsOnEntity(
        array $neededParts,
        Colony|Spacecraft $entity,
        Spacecraft $spacecraft
    ): bool;

    /** @param array<int, int> $neededParts */
    public function consumeSpareParts(array $neededParts, Colony|Spacecraft $entity): void;

    public function determineFreeEngineerCount(Spacecraft $ship): int;

    /**
     * @return array<int, SpacecraftSystem>
     */
    public function determineRepairOptions(SpacecraftWrapperInterface $wrapper): array;

    public function createRepairTask(
        Spacecraft $ship,
        SpacecraftSystemTypeEnum $systemType,
        int $repairType,
        int $finishTime
    ): void;

    public function determineHealingPercentage(int $repairType): int;

    public function instantSelfRepair(
        Spacecraft $spacecraft,
        SpacecraftSystemTypeEnum $type,
        int $healingPercentage
    ): bool;

    public function selfRepair(
        Spacecraft $spacecraft,
        RepairTask $repairTask
    ): bool;

    public function isRepairStationBonus(ShipWrapperInterface $wrapper): bool;

    public function getRepairDuration(SpacecraftWrapperInterface $wrapper): int;

    public function getRepairDurationPreview(SpacecraftWrapperInterface $wrapper): int;
}
