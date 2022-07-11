<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PreFlight;

use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShipRepairHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    public function handle(ShipInterface $ship, ?ShipInterface $tractoringShip): void
    {
        if ($ship->cancelRepair()) {
            $this->addMessageInternal(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));
        }
    }
}
