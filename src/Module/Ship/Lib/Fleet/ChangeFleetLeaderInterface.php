<?php

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Orm\Entity\Ship;

interface ChangeFleetLeaderInterface
{
    public function change(Ship $oldLeader): void;
}
