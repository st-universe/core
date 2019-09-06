<?php

namespace Stu\Module\Ship\Lib;

use ColonyData;
use ShipData;

interface ShipCreatorInterface
{
    public function createBy(int $userId, int $shipRumpId, int $shipBuildplanId, ?ColonyData $colony = null): ShipData;
}