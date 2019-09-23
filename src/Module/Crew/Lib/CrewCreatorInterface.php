<?php

namespace Stu\Module\Crew\Lib;

use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\ShipInterface;

interface CrewCreatorInterface
{
    public function create(int $userId): CrewInterface;

    public function createShipCrew(ShipInterface $ship): void;
}