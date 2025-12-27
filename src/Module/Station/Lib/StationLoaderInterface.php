<?php

namespace Stu\Module\Station\Lib;

use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
use Stu\Orm\Entity\Station;

interface StationLoaderInterface
{
    public function getByIdAndUser(
        int $stationId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): Station;

    public function getWrapperByIdAndUser(
        int $stationId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): StationWrapperInterface;

    /** @return SourceAndTargetWrappersInterface<StationWrapperInterface> */
    public function getWrappersBySourceAndUserAndTarget(
        int $stationId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface;

    public function find(int $stationId, bool $checkForEntityLock = true): ?StationWrapperInterface;
}
