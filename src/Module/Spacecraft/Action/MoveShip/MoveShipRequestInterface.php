<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

interface MoveShipRequestInterface
{
    public function getShipId(): int;

    /**
     * @return int<1, 9>
     */
    public function getFieldCount(): int;

    public function getDestinationPosX(): int;

    public function getDestinationPosY(): int;
}
