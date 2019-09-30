<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class ProjectileWeaponShipSystem implements ShipSystemTypeInterface
{

    public function checkActivationConditions(ShipInterface $ship): bool
    {
        return $ship->getTorpedos() === false && $ship->getTorpedoCount() > 0
        ;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->setTorpedos(true);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setTorpedos(false);
    }
}
