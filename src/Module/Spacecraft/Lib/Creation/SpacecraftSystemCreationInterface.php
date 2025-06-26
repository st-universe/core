<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Spacecraft;

interface SpacecraftSystemCreationInterface
{
    /**
     * @param Collection<int, BuildplanModule> $buildplanModules
     */
    public function createShipSystemsByModuleList(
        Spacecraft $spacecraft,
        Collection $buildplanModules,
        ?SpacecraftCreationConfigInterface $spacecraftCreationConfig
    ): void;
}
