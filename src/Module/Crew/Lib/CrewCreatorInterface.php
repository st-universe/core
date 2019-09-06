<?php

namespace Stu\Module\Crew\Lib;

use CrewData;
use ShipData;

interface CrewCreatorInterface
{
    public function create(int $userId): CrewData;

    public function createShipCrew(ShipData $ship): void;
}