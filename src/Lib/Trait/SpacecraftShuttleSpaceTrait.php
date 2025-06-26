<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;

trait SpacecraftShuttleSpaceTrait
{
    protected function hasFreeShuttleSpace(Spacecraft $spacecraft): bool
    {
        return $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            && $spacecraft->getRump()->getShuttleSlots() - $this->getStoredShuttleCount($spacecraft) > 0;
    }

    protected function getStoredShuttleCount(Spacecraft $spacecraft): int
    {
        return $spacecraft->getStorage()
            ->filter(fn(Storage $storage): bool => $storage->getCommodity()->isShuttle())
            ->reduce(fn(int $value, Storage $storage): int => $value + $storage->getAmount(), 0);
    }
}
