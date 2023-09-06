<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\MapInterface;

interface CheckAstronomicalWaypointsInterface
{
    public function checkWaypoint(
        ShipInterface $ship,
        StarSystemMapInterface|MapInterface $nextField,
        InformationWrapper $informations
    ): void;
}
