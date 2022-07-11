<?php

namespace Stu\Component\Ship\UpdateLocation\Handler;

use Stu\Orm\Entity\ShipInterface;

interface UpdateLocationHandlerInterface
{
    public function handle(ShipInterface $ship, ?ShipInterface $tractoringShip): void;

    public function clearMessages(): void;

    public function getInternalMsg(): array;
}
