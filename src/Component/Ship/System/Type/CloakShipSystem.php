<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Orm\Entity\ShipInterface;

final class CloakShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->astroEntryLib = $astroEntryLib;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->isTractoring()) {
            $reason = _('das Schiff den Traktorstrahl aktiviert hat');
            return false;
        }

        if ($ship->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($ship->getSubspaceState()) {
            $reason = _('die Subraumfeldsensoren aktiv sind');
            return false;
        }

        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
            $reason = _('die Alarmstufe Rot ist');
            return false;
        }

        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 10;
    }

    public function getEnergyConsumption(): int
    {
        return 8;
    }

    public function getPriority(): int
    {
        return ShipSystemTypeEnum::SYSTEM_PRIORITIES[ShipSystemTypeEnum::SYSTEM_CLOAK];
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->deactivateTractorBeam(); //active deactivation
        $ship->setDockedTo(null);
        $ship->cancelRepair();

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)->setMode(ShipSystemModeEnum::MODE_OFF);
        }
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->setMode(ShipSystemModeEnum::MODE_OFF);
        }
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->setMode(ShipSystemModeEnum::MODE_OFF);
        }

        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_CLOAK)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_CLOAK)->setMode(ShipSystemModeEnum::MODE_OFF);
    }
}
