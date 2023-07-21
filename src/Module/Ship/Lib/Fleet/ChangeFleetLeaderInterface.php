<?php

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Orm\Entity\ShipInterface;

interface ChangeFleetLeaderInterface
{
    public function change(ShipInterface $oldLeader): void;
}
