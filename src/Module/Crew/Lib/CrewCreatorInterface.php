<?php

namespace Stu\Module\Crew\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;

interface CrewCreatorInterface
{
    public function create(int $userId, ?ColonyInterface $colony = null): ShipCrewInterface;

    public function createShipCrew(ShipInterface $ship, ColonyInterface|ShipInterface $crewProvider, ?int $amount = null): void;
}
