<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class SubspaceSensorShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 30;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 15;
    }
}
