<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Override;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\Spacecraft;

final class StationLoader implements StationLoaderInterface
{
    /**
     * @param SpacecraftLoaderInterface<StationWrapperInterface> $spacecraftLoader
     */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader
    ) {}

    #[Override]
    public function getByIdAndUser(
        int $stationId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): Station {

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            $stationId,
            $userId,
            $allowUplink,
            $checkForEntityLock
        );

        if (!$spacecraft instanceof Station) {
            throw new RuntimeException(sprintf('stationId %d is not a station', $stationId));
        }

        return $spacecraft;
    }

    #[Override]
    public function getWrapperByIdAndUser(
        int $stationId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): StationWrapperInterface {

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $stationId,
            $userId,
            $allowUplink,
            $checkForEntityLock
        );

        if (!$wrapper instanceof StationWrapperInterface) {
            throw new RuntimeException(sprintf('stationId %d is not a station', $stationId));
        }

        return $wrapper;
    }

    #[Override]
    public function getWrappersBySourceAndUserAndTarget(
        int $stationId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface {

        return $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $stationId,
            $userId,
            $targetId,
            $allowUplink,
            $checkForEntityLock
        );
    }

    #[Override]
    public function find(int $stationId, bool $checkForEntityLock = true): ?StationWrapperInterface
    {
        $wrapper = $this->spacecraftLoader->find(
            $stationId,
            $checkForEntityLock
        );

        if ($wrapper !== null && !$wrapper instanceof StationWrapperInterface) {
            throw new RuntimeException(sprintf('stationId %d is not a station', $stationId));
        }

        return $wrapper;
    }

    public function save(Spacecraft $station): void
    {
        $this->spacecraftLoader->save($station);
    }
}
