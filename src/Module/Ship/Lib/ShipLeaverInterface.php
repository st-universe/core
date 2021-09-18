<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipLeaverInterface
{
    public function evacuate(ShipInterface $ship): string;

    public function dumpCrewman(ShipCrewInterface $shipCrew): string;
}
