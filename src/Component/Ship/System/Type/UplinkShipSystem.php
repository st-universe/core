<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class UplinkShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const MAX_FOREIGNERS = 3;

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_UPLINK;
    }

    public function checkActivationConditions(ShipWrapperInterface $wrapper, ?string &$reason): bool
    {
        $ship = $wrapper->get();

        if (!$this->hasForeignCrew($ship)) {
            $reason = _('keine fremde Crew an Bord ist');
            return false;
        }

        return true;
    }

    private function hasForeignCrew(ShipInterface $ship): bool
    {
        foreach ($ship->getCrewAssignments() as $shipCrew) {
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
}
