<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class EpsShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    //TODO ebatt cooldown into data?


    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getPriority(): int
    {
        return ShipSystemTypeEnum::SYSTEM_PRIORITIES[ShipSystemTypeEnum::SYSTEM_EPS];
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        $ship->setEps(0);
    }

    public function handleDamage(ShipInterface $ship): void
    {
        if ($ship->getEps() > $ship->getMaxEps()) {
            $ship->setEps($ship->getMaxEps());
        }
    }
}
