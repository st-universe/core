<?php

namespace Stu\Module\Ship\Lib;


use Stu\Orm\Entity\ShipCrewInterface;

interface ShipLeaverInterface
{
    public function evacuate(ShipWrapperInterface $wrapper): string;

    public function dumpCrewman(ShipCrewInterface $shipCrew): string;
}
