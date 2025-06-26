<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;

interface ShipRetrofitInterface
{
    public function updateBy(Ship $ship, SpacecraftBuildplan $newBuildplan, Colony $colony): void;
}
