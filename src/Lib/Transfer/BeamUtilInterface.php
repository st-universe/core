<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Lib\Information\InformationWrapper;
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
        InformationWrapper $informations
    ): void;

    public function isDockTransfer(
        ShipInterface|ColonyInterface $source,
        ShipInterface|ColonyInterface $target
    ): bool;
}
