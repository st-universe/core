<?php

namespace Stu\Component\Ship\Selfrepair;

use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;

interface SelfrepairUtilInterface
{
    public function determineFreeEngineerCount(ShipInterface $ship): int;

    public function determineRepairOptions(ShipInterface $ship): array;

    public function createRepairTask(
        ShipInterface $ship,
        int $systemType,
        int $repairType,
        int $finishTime
    ): bool;

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
