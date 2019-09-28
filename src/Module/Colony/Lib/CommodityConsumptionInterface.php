<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;

interface CommodityConsumptionInterface
{
    public function getConsumption(ColonyInterface $colony): array;
}
