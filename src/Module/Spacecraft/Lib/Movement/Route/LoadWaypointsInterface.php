<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Location;

interface LoadWaypointsInterface
{
    /**
     * @return Collection<int, Location>
     */
    public function load(
        Location $start,
        Location $destination
    ): Collection;
}
