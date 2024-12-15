<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

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
    ): SpacecraftInterface;

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

    public function save(SpacecraftInterface $spacecraft): void;
}
