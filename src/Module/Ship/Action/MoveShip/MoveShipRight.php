<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Orm\Entity\ShipInterface;

final class MoveShipRight extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE_RIGHT';

    protected function getPosX(ShipInterface $ship, int $fields): int
    {
        return $ship->getPosX() + $fields;
    }

    protected function getPosY(ShipInterface $ship, int $fields): int
    {
        return $ship->getPosY();
    }
}
