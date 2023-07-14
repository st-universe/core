<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminator;
use Stu\Module\Ship\Lib\Movement\Component\ShipMovementBlockingDeterminatorInterface;

/**
 * Factory for creating components related to the ship movement process
 */
final class ShipMovementComponentsFactory implements ShipMovementComponentsFactoryInterface
{
    public function createShipMovementBlockingDeterminator(): ShipMovementBlockingDeterminatorInterface
    {
        return new ShipMovementBlockingDeterminator();
    }
}
