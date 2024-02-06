<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface PirateNavigationInterface
{
    public function navigateToTarget(
        FleetWrapperInterface $fleet,
        MapInterface|StarSystemMapInterface|StarSystemInterface $target
    ): bool;
}
