<?php

namespace Stu\Module\Spacecraft\Lib\Damage;

use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ApplyDamageInterface
{
    public function damage(
        DamageWrapper $damage_wrapper,
        SpacecraftWrapperInterface $shipWrapper,
        InformationInterface $informations
    ): void;
}
