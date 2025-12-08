<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class WarpcoreChargeTransferShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER;
    }

    #[\Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }
}
