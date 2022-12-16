<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShieldShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private CancelRepairInterface $cancelRepair;

    public function __construct(
        CancelRepairInterface $cancelRepair
    ) {
        $this->cancelRepair = $cancelRepair;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->isTractoring()) {
            $reason = _('der Traktorstrahl aktiviert ist');
            return false;
        }

        if ($ship->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($ship->getShield() === 0) {
            $reason = _('die Schildemitter erschöpft sind');
            return false;
        }

        return true;
    }

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        $this->cancelRepair->cancelRepair($ship);
        $ship->setDockedTo(null);
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        $ship->setShield(0);
    }

    public function handleDamage(ShipInterface $ship): void
    {
        if ($ship->getShield() > $ship->getMaxShield()) {
            $ship->setShield($ship->getMaxShield());
        }
    }
}
