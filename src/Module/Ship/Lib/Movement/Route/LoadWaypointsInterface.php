<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\LocationInterface;

interface LoadWaypointsInterface
{
    /**
     * @return Collection<int, LocationInterface>
     */
    public function load(
        LocationInterface $start,
        LocationInterface $destination
    ): Collection;
}
