<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\StationShipRepair;

final class PassiveRepairProgressBuilder
{
    public function __construct(
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly PassiveRepairProjectionCalculatorInterface $passiveRepairProjectionCalculator
    ) {}

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     * @return array<PassiveRepairProgressWrapper>
     */
    public function build(array $jobs, int $activeSlotCount, bool $isRepairStationBonus): array
    {
        $projections = $this->passiveRepairProjectionCalculator->calculate(
            $jobs,
            $activeSlotCount,
            $isRepairStationBonus
        );
        $result = [];

        foreach ($jobs as $job) {
            $wrapper = $this->spacecraftWrapperFactory->wrapShip($job->getShip());
            $projection = $projections[$job->getShipId()];

            $result[] = new PassiveRepairProgressWrapper(
                $wrapper,
                $projection->getFinishTime(),
                $projection->getPotentialNextWaveTime(),
                $projection->getPotentialFinishTime(),
                $projection->getStopDate(),
                $projection->isStopped(),
                $projection->isActiveRepair(),
                $job instanceof ColonyShipRepair ? $job->getFieldId() : null
            );
        }

        return $result;
    }
}
