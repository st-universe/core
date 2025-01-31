<?php

namespace Stu\Component\Spacecraft\Repair;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface RepairUtilInterface
{
    /**
     * @return array<int, int>
     */
    public function determineSpareParts(SpacecraftWrapperInterface $wrapper, bool $tickBased): array;

    /** @param array<int, int> $neededParts */
    public function enoughSparePartsOnEntity(
        array $neededParts,
        ColonyInterface|SpacecraftInterface $entity,
        SpacecraftInterface $spacecraft
    ): bool;

    /** @param array<int, int> $neededParts */
    public function consumeSpareParts(array $neededParts, ColonyInterface|SpacecraftInterface $entity): void;

    public function determineFreeEngineerCount(SpacecraftInterface $ship): int;

    /**
     * @return array<int, SpacecraftSystemInterface>
     */
    public function determineRepairOptions(SpacecraftWrapperInterface $wrapper): array;

    public function createRepairTask(
        SpacecraftInterface $ship,
        SpacecraftSystemTypeEnum $systemType,
        int $repairType,
        int $finishTime
    ): void;

    public function determineHealingPercentage(int $repairType): int;

    public function instantSelfRepair(
        SpacecraftInterface $spacecraft,
        SpacecraftSystemTypeEnum $type,
        int $healingPercentage
    ): bool;

    public function selfRepair(
        SpacecraftInterface $spacecraft,
        RepairTaskInterface $repairTask
    ): bool;

    public function isRepairStationBonus(ShipWrapperInterface $wrapper): bool;

    public function getRepairDuration(SpacecraftWrapperInterface $wrapper): int;

    public function getRepairDurationPreview(SpacecraftWrapperInterface $wrapper): int;
}
