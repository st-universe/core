<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Orm\Entity\ShipInterface;

interface FightLibInterface
{
    public function ready(ShipInterface $ship): array;

    public function filterInactiveShips(array $base): array;
}
