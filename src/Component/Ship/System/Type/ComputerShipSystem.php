<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ComputerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_COMPUTER;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }

    #[Override]
    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_ON;
    }
}
