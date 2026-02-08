<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\Spacecraft;

/**
 * @template T of SpacecraftWrapperInterface
 */
interface SpacecraftLoaderInterface
{
    public function getByIdAndUser(
        int $spacecraftId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): Spacecraft;

    public function getWrapperByIdAndUser(
        int $spacecraftId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SpacecraftWrapperInterface;

    /**
     * @return SourceAndTargetWrappersInterface<T>
     */
    public function getWrappersBySourceAndUserAndTarget(
        int $spacecraftId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface;

    public function find(int $spacecraftId, bool $checkForEntityLock = true): ?SpacecraftWrapperInterface;
}
