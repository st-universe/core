<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Module;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\Spacecraft;

interface ModuleRecyclingInterface
{
    public function retrieveSomeModules(
        Spacecraft $spacecraft,
        EntityWithStorageInterface $entity,
        InformationInterface $information,
        int $recyclingChance = 50
    ): void;
}
