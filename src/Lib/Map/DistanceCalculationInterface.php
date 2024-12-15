<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface DistanceCalculationInterface
{
    public function shipToShipDistance(SpacecraftInterface $one, SpacecraftInterface $other): int;

    public function spacecraftToColonyDistance(SpacecraftInterface $spacecraft, ColonyInterface $colony): int;
}
