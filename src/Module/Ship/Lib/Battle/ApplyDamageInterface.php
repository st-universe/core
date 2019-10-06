<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\DamageWrapper;
use Stu\Orm\Entity\ShipInterface;

interface ApplyDamageInterface
{
    public function damage(DamageWrapper $damage_wrapper, ShipInterface $ship): array;
}
