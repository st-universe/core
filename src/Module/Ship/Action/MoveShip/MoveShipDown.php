<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Orm\Entity\ShipInterface;

final class MoveShipDown extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE_DOWN';

    protected function getPosX(ShipInterface $ship, int $fields): int
    {
        return $ship->getPosX();
    }

    protected function getPosY(ShipInterface $ship, int $fields): int
    {
        return $ship->getPosY() + $fields;
    }
}
