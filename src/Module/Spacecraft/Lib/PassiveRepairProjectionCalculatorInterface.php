<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\StationShipRepair;

interface PassiveRepairProjectionCalculatorInterface
{
    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     * @return array<int, PassiveRepairProjection>
     */
    public function calculate(array $jobs, int $activeSlotCount, bool $isRepairStationBonus): array;

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    public function getPotentialFinishTime(
        array $jobs,
        int $activeSlotCount,
        bool $isRepairStationBonus,
        int $shipId
    ): int;
}
