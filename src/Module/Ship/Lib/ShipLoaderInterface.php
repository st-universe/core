<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipLoaderInterface
{
    public function getByIdAndUser(int $shipId, int $userId, bool $allowUplink = false): ShipInterface;

    public function getWrapperByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false
    ): ShipWrapperInterface;

    /**
     * @return ShipWrapperInterface[]
     */
    public function getWrappersByIdAndUserAndTarget(int $shipId, int $userId, int $targetId, bool $allowUplink = false): array;

    public function find(int $shipId): ?ShipWrapperInterface;

    public function save(ShipInterface $ship): void;
}
