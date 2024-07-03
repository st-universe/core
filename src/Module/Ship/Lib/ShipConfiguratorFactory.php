<?php

namespace Stu\Module\Ship\Lib;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

class ShipConfiguratorFactory implements ShipConfiguratorFactoryInterface
{
    public function __construct(private TorpedoTypeRepositoryInterface $torpedoTypeRepository, private ShipTorpedoManagerInterface $torpedoManager, private CrewCreatorInterface $crewCreator, private ShipCrewRepositoryInterface $shipCrewRepository, private ShipRepositoryInterface $shipRepository, private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper, private GameControllerInterface $game)
    {
    }

    #[Override]
    public function createShipConfigurator(ShipWrapperInterface $wrapper): ShipConfiguratorInterface
    {
        return new ShipConfigurator(
            $wrapper,
            $this->torpedoTypeRepository,
            $this->torpedoManager,
            $this->crewCreator,
            $this->shipCrewRepository,
            $this->shipRepository,
            $this->activatorDeactivatorHelper,
            $this->game
        );
    }
}
