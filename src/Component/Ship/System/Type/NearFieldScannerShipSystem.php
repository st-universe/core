<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class NearFieldScannerShipSystem implements ShipSystemTypeInterface
{
    public function isAlreadyActive(ShipInterface $ship): bool
    {
        return $ship->getNbs();
    }

    public function checkActivationConditions(ShipInterface $ship): bool
    {
        return $ship->getNbs() === false
        ;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->setNbs(true);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setNbs(false);
    }
}
