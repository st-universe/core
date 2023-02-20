<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Orm\Entity\ShipInterface;

final class MoveShip extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE';

    protected function getPosX(ShipInterface $ship): int
    {
        return $this->moveShipRequest->getDestinationPosX();
    }

    protected function getPosY(ShipInterface $ship): int
    {
        return $this->moveShipRequest->getDestinationPosY();
    }
}
