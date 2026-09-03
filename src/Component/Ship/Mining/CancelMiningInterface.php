<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Mining;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface CancelMiningInterface
{
    public function cancelMining(ShipWrapperInterface $wrapper): bool;
}
