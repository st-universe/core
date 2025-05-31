<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StorageInterface;

trait SpacecraftShuttleSpaceTrait
{
    protected function hasFreeShuttleSpace(SpacecraftInterface $spacecraft): bool
    {
        return $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            && $spacecraft->getRump()->getShuttleSlots() - $this->getStoredShuttleCount($spacecraft) > 0;
    }

    protected function getStoredShuttleCount(SpacecraftInterface $spacecraft): int
    {
        return $spacecraft->getStorage()
            ->filter(fn(StorageInterface $storage): bool => $storage->getCommodity()->isShuttle())
            ->reduce(fn(int $value, StorageInterface $storage): int => $value + $storage->getAmount(), 0);
    }
}
