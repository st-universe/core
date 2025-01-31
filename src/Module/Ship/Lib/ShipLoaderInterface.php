<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipLoaderInterface
{
    public function getByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): ShipInterface;

    public function getWrapperByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): ShipWrapperInterface;

    /** @return SourceAndTargetWrappersInterface<ShipWrapperInterface> */
    public function getWrappersBySourceAndUserAndTarget(
        int $shipId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface;

    public function find(int $shipId, bool $checkForEntityLock = true): ?ShipWrapperInterface;

    public function save(ShipInterface $ship): void;
}
