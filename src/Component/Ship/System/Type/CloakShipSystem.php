<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class CloakShipSystem implements ShipSystemTypeInterface
{
    public function isAlreadyActive(ShipInterface $ship): bool
    {
        return $ship->getCloakState();
    }

    public function checkActivationConditions(ShipInterface $ship): bool
    {
        return $ship->getCloakState() === false && $ship->isCloakable() === true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->deactivateTraktorBeam();
        $ship->setDockedTo(null);
        $ship->setShieldState(false);
        $ship->setCloakState(true);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setCloakState(false);
    }
}
