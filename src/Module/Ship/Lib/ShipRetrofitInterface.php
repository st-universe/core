<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;

interface ShipRetrofitInterface
{
    public function updateBy(ShipInterface $ship, ShipBuildplanInterface $newBuildplan, ColonyInterface $colony): void;
}