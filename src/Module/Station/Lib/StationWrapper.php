<?php

namespace Stu\Module\Station\Lib;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\AggregationSystemSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\StationInterface;

/**
 * @extends SpacecraftWrapper<StationInterface>
 */
class StationWrapper extends SpacecraftWrapper implements StationWrapperInterface
{
    #[Override]
    public function get(): StationInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        return null;
    }

    #[Override]
    public function canBeScrapped(): bool
    {
        $station = $this->get();

        return $station->getState() !== SpacecraftStateEnum::UNDER_SCRAPPING;
    }

    #[Override]
    public function getAggregationSystemSystemData(): ?AggregationSystemSystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM,
            AggregationSystemSystemData::class
        );
    }
}
