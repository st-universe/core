<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface RandomSystemEntryInterface
{
    public function getRandomEntryPoint(SpacecraftWrapperInterface $wrapper, StarSystemInterface $system): StarSystemMapInterface;
}
