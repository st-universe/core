<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface TransferTargetLoaderInterface
{
    public function loadTarget(
        int $targetId,
        bool $isColonyTarget,
        bool $checkForEntityLock = true
    ): ShipInterface|ColonyInterface;
}
