<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface RandomSystemEntryInterface
{
    public function getRandomEntryPoint(ShipInterface $ship, StarSystemInterface $system): StarSystemMapInterface;
}
