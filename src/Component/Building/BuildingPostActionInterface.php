<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;

interface BuildingPostActionInterface
{
    public function handleDeactivation(
        BuildingInterface $building,
        ColonyInterface $colony
    ): void;

    public function handleActivation(
        BuildingInterface $building,
        ColonyInterface $colony
    ): void;
}
