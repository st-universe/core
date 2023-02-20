<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Orm\Entity\ShipInterface;

/**
 * Performs movement to the right
 */
final class MoveShipRight extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE_RIGHT';

    protected function getPosX(ShipInterface $ship): int
    {
        return $ship->getPosX() + $this->moveShipRequest->getFieldCount();
    }

    protected function getPosY(ShipInterface $ship): int
    {
        return $ship->getPosY();
    }
}
