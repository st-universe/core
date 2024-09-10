<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class BussardCollectorShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(private ShipStateChangerInterface $shipStateChanger) {}

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->isTractoring()) {
            $reason = _('das Schiff den Traktorstrahl aktiviert hat');
            return false;
        }

        if ($ship->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
            $reason = _('die Alarmstufe Rot ist');
            return false;
        }

        if ($ship->getDockedTo()) {
            $reason = _('das Schiff angedockt ist');
            return false;
        }

        if ($ship->getWarpDriveState()) {
            $reason = _('das Schiff im Warpantrieb ist');
            return false;
        }

        if (!$ship->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        if ($ship->getCloakState()) {
            $reason = _('das Schiff getarnt ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        if ($ship->isTractoring()) {
            $manager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
        }

        $ship->setDockedTo(null);
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)->setMode(ShipSystemModeEnum::MODE_OFF);
        }

        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 10;
    }
}
