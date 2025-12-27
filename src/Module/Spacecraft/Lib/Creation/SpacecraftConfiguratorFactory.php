<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Component\Spacecraft\System\Control\AlertStateManagerInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftStartupInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

class SpacecraftConfiguratorFactory implements SpacecraftConfiguratorFactoryInterface
{
    public function __construct(
        private readonly TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private readonly ShipTorpedoManagerInterface $torpedoManager,
        private readonly CrewCreatorInterface $crewCreator,
        private readonly CrewAssignmentRepositoryInterface $shipCrewRepository,
        private readonly AlertStateManagerInterface $alertStateManager,
        private readonly SpacecraftStartupInterface $spacecraftStartup
    ) {}

    #[\Override]
    public function createSpacecraftConfigurator($wrapper): SpacecraftConfiguratorInterface
    {
        return new SpacecraftConfigurator(
            $wrapper,
            $this->torpedoTypeRepository,
            $this->torpedoManager,
            $this->crewCreator,
            $this->shipCrewRepository,
            $this->alertStateManager,
            $this->spacecraftStartup
        );
    }
}
