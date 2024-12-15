<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Spacecraft\Lib\Creation\ShipSpecialSystemsProvider;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreatorInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class ShipCreator implements ShipCreatorInterface
{
    /** @param SpacecraftCreatorInterface<ShipWrapperInterface> $spacecraftCreator */
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository,
        private SpacecraftCreatorInterface $spacecraftCreator
    ) {}

    public function createBy(
        int $userId,
        int $rumpId,
        int $buildplanId
    ): SpacecraftConfiguratorInterface {

        $configurator = $this->spacecraftCreator->createBy(
            $userId,
            $rumpId,
            $buildplanId,
            new ShipSpecialSystemsProvider($this->buildplanRepository, $buildplanId)
        );

        return $configurator;
    }
}
