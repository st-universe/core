<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;

interface ShipRetrofitInterface
{
    public function updateBy(ShipInterface $ship, SpacecraftBuildplanInterface $newBuildplan, ColonyInterface $colony): void;
}
