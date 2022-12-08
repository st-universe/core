<?php

namespace Stu\Component\Ship\Repair;

use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;

interface RepairUtilInterface
{
    public function determineSpareParts(ShipInterface $ship): array;

    public function enoughSparePartsOnEntity(array $neededParts, $entity, bool $isColony, ShipInterface $ship): bool;

    public function consumeSpareParts(array $neededParts, $entity, bool $isColony): void;

    public function determineFreeEngineerCount(ShipInterface $ship): int;

    public function determineRepairOptions(ShipInterface $ship): array;

    public function createRepairTask(
        ShipInterface $ship,
        int $systemType,
        int $repairType,
        int $finishTime
    ): void;

    public function determineHealingPercentage(int $repairType): int;

    public function instantSelfRepair(
        $ship,
        $systemType,
        $healingPercentage
    ): bool;

    public function selfRepair(
        ShipInterface $ship,
        RepairTaskInterface $repairTask
    ): bool;
}
