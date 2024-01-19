<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;

class ManagerProviderFactory implements ManagerProviderFactoryInterface
{
    private CrewCreatorInterface $crewCreator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(
        CrewCreatorInterface $crewCreator,
        ColonyLibFactoryInterface $colonyLibFactory,
        TroopTransferUtilityInterface $troopTransferUtility,
        ColonyStorageManagerInterface $colonyStorageManager,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->crewCreator = $crewCreator;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->shipStorageManager = $shipStorageManager;
    }

    public function getManagerProviderColony(ColonyInterface $colony): ManagerProviderInterface
    {
        return new ManagerProviderColony(
            $colony,
            $this->crewCreator,
            $this->colonyLibFactory,
            $this->colonyStorageManager,
            $this->troopTransferUtility
        );
    }

    public function getManagerProviderStation(ShipWrapperInterface $wrapper): ManagerProviderInterface
    {
        return new ManagerProviderStation(
            $wrapper,
            $this->crewCreator,
            $this->troopTransferUtility,
            $this->shipStorageManager
        );
    }
}
