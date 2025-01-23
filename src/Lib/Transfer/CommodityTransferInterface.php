<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;

interface CommodityTransferInterface
{
    public function transferCommodity(
        int $commodityId,
        string|int $wantedAmount,
        SpacecraftWrapperInterface|ColonyInterface $subject,
        EntityWithStorageInterface $source,
        EntityWithStorageInterface $target,
        InformationInterface $information
    ): bool;

    public function isDockTransfer(
        EntityWithStorageInterface $source,
        EntityWithStorageInterface $target
    ): bool;
}
