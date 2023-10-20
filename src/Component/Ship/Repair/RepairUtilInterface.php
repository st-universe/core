<?php

namespace Stu\Component\Ship\Repair;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;

interface RepairUtilInterface
{
    /**
     * @return array<int, int>
     */
    public function determineSpareParts(ShipWrapperInterface $wrapper): array;

    public function enoughSparePartsOnEntity(array $neededParts, $entity, bool $isColony, ShipInterface $ship): bool;

    public function consumeSpareParts(array $neededParts, $entity, bool $isColony): void;

    public function determineFreeEngineerCount(ShipInterface $ship): int;

    /**
     * @return array<int, ShipSystemInterface>
     */
    public function determineRepairOptions(ShipWrapperInterface $wrapper): array;

    public function createRepairTask(
        ShipInterface $ship,
        ShipSystemTypeEnum $systemType,
        int $repairType,
        int $finishTime
    ): void;

    public function determineHealingPercentage(int $repairType): int;

    public function instantSelfRepair(
        ShipInterface $ship,
        ShipSystemTypeEnum $type,
        int $healingPercentage
    ): bool;

    public function selfRepair(
        ShipInterface $ship,
        RepairTaskInterface $repairTask
    ): bool;

    public function getRepairDuration(ShipWrapperInterface $wrapper): int;

    public function getRepairDurationPreview(ShipWrapperInterface $wrapper): int;
}
