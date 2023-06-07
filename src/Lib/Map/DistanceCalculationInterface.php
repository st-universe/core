<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface DistanceCalculationInterface
{
    public function shipToShipDistance(ShipInterface $ship, ShipInterface $station): int;

    public function shipToColonyDistance(ShipInterface $ship, ColonyInterface $colony): int;
}
