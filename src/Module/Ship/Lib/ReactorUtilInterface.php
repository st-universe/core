<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface ReactorUtilInterface
{
    public function storageContainsNeededCommodities($storage, bool $isWarpcore = true): bool;

    public function loadReactor(
        ShipInterface $ship,
        int $additionalLoad,
        ?ColonyInterface $colony = null,
        ?ShipInterface $station = null,
        bool $isWarpcore = true
    ): ?string;
}
