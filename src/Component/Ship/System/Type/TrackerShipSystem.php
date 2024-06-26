<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class TrackerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TRACKER;
    }

    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if (!$ship->getLss()) {
            $reason = _('die Langstreckensensoren nicht aktiv sind');
            return false;
        }

        if (!$ship->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        return true;
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $this->reset($wrapper);
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getCooldownSeconds(): ?int
    {
        return TimeConstants::ONE_HOUR_IN_SECONDS;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    public function getEnergyConsumption(): int
    {
        return 7;
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $this->reset($wrapper);
    }

    private function reset(ShipWrapperInterface $wrapper): void
    {
        $trackerSystemData = $wrapper->getTrackerSystemData();
        if ($trackerSystemData !== null) {
            $trackerSystemData->setTarget(null)->update();
        }
    }
}
