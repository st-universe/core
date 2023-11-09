<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class SingularityShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }

    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_ON;
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)->setMode(ShipSystemModeEnum::MODE_OFF);
        }
    }
}
