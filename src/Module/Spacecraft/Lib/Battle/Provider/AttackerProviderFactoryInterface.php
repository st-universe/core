<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;

interface AttackerProviderFactoryInterface
{
    public function getSpacecraftAttacker(SpacecraftWrapperInterface $wrapper): SpacecraftAttacker;
    public function getEnergyPhalanxAttacker(ColonyInterface $colony): EnergyAttackerInterface;
    public function getProjectilePhalanxAttacker(ColonyInterface $colony): ProjectileAttackerInterface;
}
