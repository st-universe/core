<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

class SpacecraftConfiguratorFactory implements SpacecraftConfiguratorFactoryInterface
{
    public function __construct(
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private ShipTorpedoManagerInterface $torpedoManager,
        private CrewCreatorInterface $crewCreator,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
        private GameControllerInterface $game
    ) {}

    #[Override]
    public function createSpacecraftConfigurator($wrapper): SpacecraftConfiguratorInterface
    {
        return new SpacecraftConfigurator(
            $wrapper,
            $this->torpedoTypeRepository,
            $this->torpedoManager,
            $this->crewCreator,
            $this->shipCrewRepository,
            $this->spacecraftRepository,
            $this->activatorDeactivatorHelper,
            $this->game
        );
    }
}
