<?php

namespace Stu\Module\Crew\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\ShipInterface;

interface CrewCreatorInterface
{
    public function create(int $userId, ?ColonyInterface $colony = null): CrewInterface;

    public function createShipCrew(ShipInterface $ship, ?ColonyInterface $colony = null, ?ShipInterface $station = null): void;
}
