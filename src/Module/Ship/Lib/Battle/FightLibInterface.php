<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface FightLibInterface
{
    public function ready(ShipWrapperInterface $wrapper): array;

    /**
     * @param ShipWrapperInterface[] $base
     * 
     * @return ShipWrapperInterface[]
     */
    public function filterInactiveShips(array $base): array;
}
