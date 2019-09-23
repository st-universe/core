<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipStorageManagerInterface
{

    public function lowerStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void;

    public function upperStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void;
}