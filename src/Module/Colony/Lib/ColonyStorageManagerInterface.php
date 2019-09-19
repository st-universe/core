<?php

namespace Stu\Module\Colony\Lib;

use ColonyData;
use Stu\Orm\Entity\CommodityInterface;

interface ColonyStorageManagerInterface
{
    public function lowerStorage(ColonyData $colony, CommodityInterface $commodity, int $amount): void;

    public function upperStorage(ColonyData $colony, CommodityInterface $commodity, int $amount): void;
}