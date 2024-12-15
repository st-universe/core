<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class NearFieldScannerShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(
        private AstroEntryLibInterface $astroEntryLib,
        private TrackerDeviceManagerInterface $trackerDeviceManager
    ) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SYSTEM_NBS;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->getUser()->hasColony() && !$spacecraft->getUser()->isNpc()) {
            $reason = _('noch keine Kolonie kolonisiert wurde');
            return false;
        }

        return true;
    }

    #[Override]
    public function checkDeactivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        if ($wrapper->get()->getAlertState() === SpacecraftAlertStateEnum::ALERT_RED) {
            $reason = _('die Alarmstufe Rot ist');
            return false;
        }

        $trackerData = $wrapper instanceof ShipWrapperInterface ? $wrapper->getTrackerSystemData() : null;

        //not possible if tracker active
        if ($trackerData !== null && $trackerData->targetId !== null) {
            $reason = _('der Tracker aktiv ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        $spacecraft->getShipSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);

        //other consequences
        if ($spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(SpacecraftSystemModeEnum::MODE_OFF);

            if ($spacecraft->getState() === SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
                $this->astroEntryLib->cancelAstroFinalizing($wrapper);
            }
        }
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)) {
            $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(SpacecraftSystemModeEnum::MODE_OFF);

            if ($spacecraft->getState() === SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
                $this->astroEntryLib->cancelAstroFinalizing($wrapper);
            }
        }

        if ($wrapper instanceof ShipWrapperInterface) {
            $this->trackerDeviceManager->deactivateTrackerIfActive($wrapper, false);
        }
    }
}
