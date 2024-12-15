<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface RandomSystemEntryInterface
{
    public function getRandomEntryPoint(SpacecraftInterface $spacecraft, StarSystemInterface $system): StarSystemMapInterface;
}
