<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class ShipLoader implements ShipLoaderInterface
{
    /**
     * @param SpacecraftLoaderInterface<ShipWrapperInterface> $spacecraftLoader
     */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader
    ) {}

    #[Override]
    public function getByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): ShipInterface {

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            $shipId,
            $userId,
            $allowUplink,
            $checkForEntityLock
        );

        if (!$spacecraft instanceof ShipInterface) {
            throw new RuntimeException(sprintf('shipId %d is not a ship', $shipId));
        }

        return $spacecraft;
    }

    #[Override]
    public function getWrapperByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): ShipWrapperInterface {

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $shipId,
            $userId,
            $allowUplink,
            $checkForEntityLock
        );

        if (!$wrapper instanceof ShipWrapperInterface) {
            throw new RuntimeException(sprintf('shipId %d is not a ship', $shipId));
        }

        return $wrapper;
    }

    #[Override]
    public function getWrappersBySourceAndUserAndTarget(
        int $shipId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface {

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId,
            $allowUplink,
            $checkForEntityLock
        );

        return $wrappers;
    }

    #[Override]
    public function find(int $shipId, bool $checkForEntityLock = true): ?ShipWrapperInterface
    {
        $wrapper = $this->spacecraftLoader->find(
            $shipId,
            $checkForEntityLock
        );

        if ($wrapper !== null && !$wrapper instanceof ShipWrapperInterface) {
            throw new RuntimeException(sprintf('shipId %d is not a ship', $shipId));
        }

        return $wrapper;
    }

    public function save(SpacecraftInterface $ship): void
    {
        $this->spacecraftLoader->save($ship);
    }
}
