<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class UplinkShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const MAX_FOREIGNERS = 3;

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if (!$this->hasForeignCrew($ship)) {
            $reason = _('keine fremde Crew an Bord ist');
            return false;
        }

        return true;
    }

    private function hasForeignCrew(ShipInterface $ship): bool
    {
        foreach ($ship->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() !== $ship->getUser()) {
                return true;
            }
        }

        return false;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getEnergyConsumption(): int
    {
        return 5;
    }

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_UPLINK)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_UPLINK)->setMode(ShipSystemModeEnum::MODE_OFF);
    }
}
