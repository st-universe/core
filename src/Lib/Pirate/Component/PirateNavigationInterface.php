<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\StarSystem;

interface PirateNavigationInterface
{
    public function navigateToTarget(
        FleetWrapperInterface $fleet,
        Location|StarSystem $target
    ): bool;
}
