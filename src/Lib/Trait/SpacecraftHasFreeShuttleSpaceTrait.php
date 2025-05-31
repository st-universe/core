<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftInterface;

trait SpacecraftHasFreeShuttleSpaceTrait
{
    protected function hasFreeShuttleSpace(SpacecraftInterface $spacecraft): bool
    {
        return $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            && $spacecraft->getRump()->getShuttleSlots() - $spacecraft->getStoredShuttleCount() > 0;
    }
}
