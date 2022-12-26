<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class LongRangeScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->astroEntryLib = $astroEntryLib;
    }

    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, &$reason): bool
    {
        $trackerData = $wrapper->getTrackerSystemData();

        //not possible if tracker active
        if ($trackerData !== null && $trackerData->getTargetWrapper() !== null) {
            $reason = _('der Tracker aktiv ist');
            return false;
        }

        return true;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_LSS)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_LSS)->setMode(ShipSystemModeEnum::MODE_OFF);

        //other consequences
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);

            if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
                $this->astroEntryLib->cancelAstroFinalizing($ship);
            }
        }
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);

            if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
                $this->astroEntryLib->cancelAstroFinalizing($ship);
            }
        }
    }
}
