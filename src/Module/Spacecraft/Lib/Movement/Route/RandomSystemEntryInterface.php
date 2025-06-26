<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;

interface RandomSystemEntryInterface
{
    public function getRandomEntryPoint(SpacecraftWrapperInterface $wrapper, StarSystem $system): StarSystemMap;
}
