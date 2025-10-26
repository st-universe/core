<?php

namespace Stu\Module\Station\Lib;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\AggregationSystemSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\Station;

/**
 * @extends SpacecraftWrapper<Station>
 */
class StationWrapper extends SpacecraftWrapper implements StationWrapperInterface
{
    #[\Override]
    public function get(): Station
    {
        return $this->spacecraft;
    }

    #[\Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        return null;
    }

    #[\Override]
    public function canBeScrapped(): bool
    {
        $station = $this->get();

        return $station->getState() !== SpacecraftStateEnum::UNDER_SCRAPPING;
    }

    #[\Override]
    public function getAggregationSystemSystemData(): ?AggregationSystemSystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM,
            AggregationSystemSystemData::class
        );
    }
}
