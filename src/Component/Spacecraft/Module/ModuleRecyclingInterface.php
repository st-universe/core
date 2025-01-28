<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Module;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface ModuleRecyclingInterface
{
    public function retrieveSomeModules(
        SpacecraftInterface $spacecraft,
        EntityWithStorageInterface $entity,
        InformationInterface $information,
        int $recyclingChance = 50
    ): void;
}
