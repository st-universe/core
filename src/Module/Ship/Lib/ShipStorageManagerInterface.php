<?php

namespace Stu\Module\Ship\Lib;

use ShipData;
use Stu\Orm\Entity\CommodityInterface;

interface ShipStorageManagerInterface
{

    public function lowerStorage(ShipData $ship, CommodityInterface $commodity, int $amount): void;

    public function upperStorage(ShipData $ship, CommodityInterface $commodity, int $amount): void;
}