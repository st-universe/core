<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface TroopTransferUtilityInterface
{
    public function getFreeQuarters(ShipInterface $ship): int;

    public function getBeamableTroopCount(ShipInterface $ship): int;
}
