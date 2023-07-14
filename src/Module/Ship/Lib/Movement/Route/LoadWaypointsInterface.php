<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface LoadWaypointsInterface
{
    /**
     * @return Collection<int, MapInterface|StarSystemMapInterface>
     */
    public function load(
        MapInterface|StarSystemMapInterface $start,
        MapInterface|StarSystemMapInterface $destination
    ): Collection;
}
