<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface CheckDestinationInterface
{
    public function validate(
        SpacecraftInterface $spacecraft,
        int $posx,
        int $posy
    ): MapInterface|StarSystemMapInterface;
}
