<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShieldShipSystem implements ShipSystemTypeInterface
{
    public function isAlreadyActive(ShipInterface $ship): bool
    {
        return $ship->getShieldState();
    }

    public function checkActivationConditions(ShipInterface $ship): bool
    {
        return $ship->getCloakState() === false
            && $ship->getShieldState() === false
            && $ship->getTraktorShip() === null
            && $ship->getShield() > 0
        ;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->cancelRepair();
        $ship->setDockedTo(null);
        $ship->setShieldState(true);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setShieldState(false);
    }
}
