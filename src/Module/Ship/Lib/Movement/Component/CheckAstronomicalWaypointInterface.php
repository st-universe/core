<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;

interface CheckAstronomicalWaypointInterface
{
    public function checkWaypoint(
        ShipInterface $ship,
        InformationWrapper $informations
    ): void;
}
