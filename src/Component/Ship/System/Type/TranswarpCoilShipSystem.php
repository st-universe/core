<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class TranswarpCoilShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL;
    }

    public function checkActivationConditions(ShipInterface $ship, ?string &$reason): bool
    {
        if ($ship->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        return true;
    }

    public function getCooldownSeconds(): ?int
    {
        return 300;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 55;
    }
}
