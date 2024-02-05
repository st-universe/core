<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipAttackCoreInterface
{
    public function foo(
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        bool &$isFleetFight,
        InformationWrapper $informations
    ): void;
}
