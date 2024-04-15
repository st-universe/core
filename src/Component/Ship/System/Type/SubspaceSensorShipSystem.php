<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class SubspaceSensorShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER;
    }

    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 30;
    }

    public function getEnergyConsumption(): int
    {
        return 15;
    }
}
