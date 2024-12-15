<?php

namespace Stu\Module\Station\Lib;

use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\AggregationSystemSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

/**
 * @extends SpacecraftWrapper<StationInterface>
 */
class StationWrapper extends SpacecraftWrapper implements StationWrapperInterface
{
    public function __construct(
        StationInterface $station,
        SpacecraftSystemManagerInterface $spacecraftSystemManager,
        SystemDataDeserializerInterface $systemDataDeserializer,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        SpacecraftStateChangerInterface $spacecraftStateChanger,
        RepairUtilInterface $repairUtil,
        StateIconAndTitle $stateIconAndTitle
    ) {
        parent::__construct(
            $station,
            $spacecraftSystemManager,
            $systemDataDeserializer,
            $torpedoTypeRepository,
            $game,
            $spacecraftWrapperFactory,
            $spacecraftStateChanger,
            $repairUtil,
            $stateIconAndTitle
        );
    }

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

        return $station->getState() !== SpacecraftStateEnum::SHIP_STATE_UNDER_SCRAPPING;
    }

    #[Override]
    public function getAggregationSystemSystemData(): ?AggregationSystemSystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM,
            AggregationSystemSystemData::class
        );
    }
}
