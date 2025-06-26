<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;

interface DistanceCalculationInterface
{
    public function shipToShipDistance(Spacecraft $one, Spacecraft $other): int;

    public function spacecraftToColonyDistance(Spacecraft $spacecraft, Colony $colony): int;
}
