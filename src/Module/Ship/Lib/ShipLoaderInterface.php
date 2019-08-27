<?php

namespace Stu\Module\Ship\Lib;

use ShipData;

interface ShipLoaderInterface
{
    public function getById($shipId): ShipData;

    public function getByIdAndUser(int $shipId, int $userId): ShipData;
}