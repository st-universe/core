<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;

trait SpacecraftWrapperCombatTrait
{
    #[Override]
    public function canFire(): bool
    {
        $ship = $this->spacecraft;
        if ($ship->isBeingAssimilated() || !$ship->getNbs() || !$ship->hasActiveWeapon()) {
            return false;
        }
        $epsSystem = $this->getEpsSystemData();
        return $epsSystem !== null && $epsSystem->getEps() !== 0;
    }
}
