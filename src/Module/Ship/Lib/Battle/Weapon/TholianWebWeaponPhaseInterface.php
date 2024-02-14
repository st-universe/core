<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(ShipWrapperInterface $wrapper, GameControllerInterface $game): InformationWrapper;
}
