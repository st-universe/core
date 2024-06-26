<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class LongRangeScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(
        private AstroEntryLibInterface $astroEntryLib,
        private TrackerDeviceManagerInterface $trackerDeviceManager
    ) {
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_LSS;
    }

    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $trackerData = $wrapper->getTrackerSystemData();

        //not possible if tracker active
        if ($trackerData !== null && $trackerData->targetId !== null) {
            $reason = _('der Tracker aktiv ist');
            return false;
        }

        return true;
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);

        //other consequences
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);

            if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
                $this->astroEntryLib->cancelAstroFinalizing($ship);
            }
        }
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);

            if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
                $this->astroEntryLib->cancelAstroFinalizing($ship);
            }
        }

        $this->trackerDeviceManager->deactivateTrackerIfExisting($wrapper);
    }
}
