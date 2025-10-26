<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Colony;

class ManagerProviderFactory implements ManagerProviderFactoryInterface
{
    public function __construct(
        private CrewCreatorInterface $crewCreator,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private StorageManagerInterface $storageManager
    ) {}

    #[\Override]
    public function getManagerProviderColony(Colony $colony): ManagerProviderInterface
    {
        return new ManagerProviderColony(
            $colony,
            $this->crewCreator,
            $this->colonyLibFactory,
            $this->storageManager,
            $this->troopTransferUtility
        );
    }

    #[\Override]
    public function getManagerProviderStation(StationWrapperInterface $wrapper): ManagerProviderInterface
    {
        return new ManagerProviderStation(
            $wrapper,
            $this->crewCreator,
            $this->troopTransferUtility,
            $this->storageManager
        );
    }
}
