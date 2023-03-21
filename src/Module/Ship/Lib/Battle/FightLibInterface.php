<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface FightLibInterface
{
    /**
     * @return string[]
     */
    public function ready(ShipWrapperInterface $wrapper): array;

    /**
     * @param ShipWrapperInterface[] $base
     *
     * @return array<int, ShipWrapperInterface>
     */
    public function filterInactiveShips(array $base): array;

    public function canFire(ShipWrapperInterface $wrapper): bool;
}
