<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftSystemCreationInterface
{
    /**
     * @param Collection<int, BuildplanModuleInterface> $buildplanModules
     */
    public function createShipSystemsByModuleList(
        SpacecraftInterface $spacecraft,
        Collection $buildplanModules,
        SpacecraftCreationConfigInterface $specialSystemsProvider
    ): void;
}
