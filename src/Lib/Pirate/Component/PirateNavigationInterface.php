<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\StarSystemInterface;

interface PirateNavigationInterface
{
    public function navigateToTarget(
        FleetWrapperInterface $fleet,
        LocationInterface|StarSystemInterface $target
    ): bool;
}
