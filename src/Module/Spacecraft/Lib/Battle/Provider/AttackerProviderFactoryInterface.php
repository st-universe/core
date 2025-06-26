<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;

interface AttackerProviderFactoryInterface
{
    public function createSpacecraftAttacker(SpacecraftWrapperInterface $wrapper, bool $isAttackingShieldsOnly = false): SpacecraftAttacker;
    public function createEnergyPhalanxAttacker(Colony $colony): EnergyAttackerInterface;
    public function createProjectilePhalanxAttacker(Colony $colony): ProjectileAttackerInterface;
}
