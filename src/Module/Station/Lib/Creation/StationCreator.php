<?php

namespace Stu\Module\Station\Lib\Creation;

use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreatorInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\ConstructionProgress;

class StationCreator implements StationCreatorInterface
{
    /** @param SpacecraftCreatorInterface<StationWrapperInterface> $spacecraftCreator */
    public function __construct(private SpacecraftCreatorInterface $spacecraftCreator) {}

    public function createBy(
        int $userId,
        int $rumpId,
        int $buildplanId,
        ConstructionProgress $progress
    ): SpacecraftConfiguratorInterface {

        return $this->spacecraftCreator->createBy(
            $userId,
            $rumpId,
            $buildplanId,
            new StationCreationConfig($progress)
        );
    }
}
