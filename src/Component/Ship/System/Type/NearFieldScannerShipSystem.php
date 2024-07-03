<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class NearFieldScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(
        private AstroEntryLibInterface $astroEntryLib,
        private TrackerDeviceManagerInterface $trackerDeviceManager
    ) {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_NBS;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if (!$ship->getUser()->hasColony() && !$ship->getUser()->isNpc()) {
            $reason = _('noch keine Kolonie kolonisiert wurde');
            return false;
        }

        return true;
    }

    #[Override]
    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        if ($wrapper->get()->getAlertState() === ShipAlertStateEnum::ALERT_RED) {
            $reason = _('die Alarmstufe Rot ist');
            return false;
        }

        $trackerData = $wrapper->getTrackerSystemData();

        //not possible if tracker active
        if ($trackerData !== null && $trackerData->targetId !== null) {
            $reason = _('der Tracker aktiv ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);

        //other consequences
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);

            if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
                $this->astroEntryLib->cancelAstroFinalizing($wrapper);
            }
        }
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);

            if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
                $this->astroEntryLib->cancelAstroFinalizing($wrapper);
            }
        }

        $this->trackerDeviceManager->deactivateTrackerIfActive($wrapper, false);
    }
}
