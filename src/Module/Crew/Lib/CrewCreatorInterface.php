<?php

namespace Stu\Module\Crew\Lib;

use ShipData;
use Stu\Orm\Entity\CrewInterface;

interface CrewCreatorInterface
{
    public function create(int $userId): CrewInterface;

    public function createShipCrew(ShipData $ship): void;
}