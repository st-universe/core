<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipStateEnum;
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

    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_LSS;
    }

    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, ?string &$reason): bool
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
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->setMode(ShipSystemModeEnum::MODE_OFF);

            $trackerSystemData = $wrapper->getTrackerSystemData();
            if ($trackerSystemData !== null) {
                $trackerSystemData->setTarget(null)->update();
            }
        }
    }
}
