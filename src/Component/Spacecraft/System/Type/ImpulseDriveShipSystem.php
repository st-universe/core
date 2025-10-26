<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;

final class ImpulseDriveShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::IMPULSEDRIVE;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }
}
