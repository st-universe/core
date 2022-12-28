<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class WebEmitterShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB;
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $this->reset($wrapper);
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB)->setMode(ShipSystemModeEnum::MODE_OFF);
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
        $wrapper->getWebEmitterSystemData()->setTarget(null)->update();
    }
}
