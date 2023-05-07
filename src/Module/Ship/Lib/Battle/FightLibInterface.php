<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

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

    public function canAttackTarget(ShipInterface $ship, ShipInterface|ShipNfsItem $nfsItem): bool;
}
