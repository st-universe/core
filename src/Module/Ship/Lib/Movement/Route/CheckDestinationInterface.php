<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface CheckDestinationInterface
{
    public function validate(
        ShipInterface $ship,
        int $posx,
        int $posy
    ): MapInterface|StarSystemMapInterface;
}
