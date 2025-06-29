<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class TrackerShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TRACKER;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->getLss()) {
            $reason = _('die Langstreckensensoren nicht aktiv sind');
            return false;
        }

        if (!$spacecraft->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        return true;
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $this->reset($wrapper);
        $wrapper->get()->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACKER)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function getCooldownSeconds(): int
    {
        return TimeConstants::ONE_HOUR_IN_SECONDS;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 7;
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $this->reset($wrapper);
    }

    private function reset(SpacecraftWrapperInterface $wrapper): void
    {
        $trackerData = $wrapper instanceof ShipWrapperInterface ? $wrapper->getTrackerSystemData() : null;
        if ($trackerData !== null) {
            $trackerData->setTarget(null)->update();
        }
    }
}
