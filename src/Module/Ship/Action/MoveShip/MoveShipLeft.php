<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Orm\Entity\ShipInterface;

/**
 * Performs movement to the left
 */
final class MoveShipLeft extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE_LEFT';

    public function performSessionCheck(): bool
    {
        return true;
    }

    protected function getPosX(ShipInterface $ship): int
    {
        return max(1, $ship->getPosX() - $this->moveShipRequest->getFieldCount());
    }

    protected function getPosY(ShipInterface $ship): int
    {
        return $ship->getPosY();
    }
}
