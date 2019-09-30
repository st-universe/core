<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class LongRangeScannerShipSystem implements ShipSystemTypeInterface
{

    public function checkActivationConditions(ShipInterface $ship): bool
    {
        return $ship->getLss() === false
        ;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->setLss(true);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setLss(false);
    }
}
