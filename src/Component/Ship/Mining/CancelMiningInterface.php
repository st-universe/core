<?php

namespace Stu\Component\Ship\Mining;

use Stu\Orm\Entity\ShipInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface CancelMiningInterface
{
    public function cancelMining(ShipInterface $ship, ShipWrapperInterface $wrapper): bool;
}
