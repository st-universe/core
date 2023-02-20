<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Orm\Entity\ShipInterface;

/**
 * Performs upwards movement
 */
final class MoveShipUp extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE_UP';

    protected function getPosX(ShipInterface $ship): int
    {
        return $ship->getPosX();
    }

    protected function getPosY(ShipInterface $ship): int
    {
        return max(1, $ship->getPosY() - $this->moveShipRequest->getFieldCount());
    }
}
