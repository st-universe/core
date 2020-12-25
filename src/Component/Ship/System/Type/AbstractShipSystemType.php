<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

abstract class AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function getEnergyConsumption(): int
    {
        return 1;
    }
}
