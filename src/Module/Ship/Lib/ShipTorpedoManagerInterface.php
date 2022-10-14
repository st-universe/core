<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

interface ShipTorpedoManagerInterface
{
    public function changeTorpedo(ShipInterface $ship, int $changeAmount, TorpedoTypeInterface $type = null);
}
