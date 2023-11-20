<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

interface BuildingPostActionInterface
{
    public function handleDeactivation(
        BuildingInterface $building,
        ColonyInterface|ColonySandboxInterface $host
    ): void;

    public function handleActivation(
        BuildingInterface $building,
        ColonyInterface|ColonySandboxInterface $host
    ): void;
}
