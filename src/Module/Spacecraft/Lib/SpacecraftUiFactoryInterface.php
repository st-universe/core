<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\WormholeRestriction;

interface SpacecraftUiFactoryInterface
{
    public function createWormholeRestrictionItem(
        WormholeRestriction $restriction
    ): WormholeRestrictionItem;
}
