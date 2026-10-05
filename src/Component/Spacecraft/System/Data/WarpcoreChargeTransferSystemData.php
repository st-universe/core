<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class WarpcoreChargeTransferSystemData extends AbstractSystemData
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER;
    }

    public function isUseable(): bool
    {
        return $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER)->getMode()->isActivated();
    }
}
