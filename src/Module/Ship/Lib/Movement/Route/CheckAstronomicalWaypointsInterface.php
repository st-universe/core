<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface CheckAstronomicalWaypointsInterface
{
    public function checkWaypoint(
        ShipInterface $ship,
        StarSystemMapInterface $nextField,
        InformationWrapper $informations
    ): void;
}
