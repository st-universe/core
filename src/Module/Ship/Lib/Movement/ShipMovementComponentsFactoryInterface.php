<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminatorInterface;

interface ShipMovementComponentsFactoryInterface
{
    public function createShipMovementBlockingDeterminator(): ShipMovementBlockingDeterminatorInterface;
}
