<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class TranswarpCoilShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        return true;
    }

    #[Override]
    public function getCooldownSeconds(): ?int
    {
        return 300;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 55;
    }
}
