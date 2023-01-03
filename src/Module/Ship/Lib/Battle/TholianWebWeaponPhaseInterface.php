<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(ShipWrapperInterface $wrapper, GameControllerInterface $game): array;
}
