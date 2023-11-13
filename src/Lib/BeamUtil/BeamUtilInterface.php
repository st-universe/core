<?php

declare(strict_types=1);

namespace Stu\Lib\BeamUtil;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface BeamUtilInterface
{
    public function transferCommodity(
        int $commodityId,
        string|int $wantedAmount,
        ShipWrapperInterface|ColonyInterface $subject,
        ShipInterface|ColonyInterface $source,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void;
}
