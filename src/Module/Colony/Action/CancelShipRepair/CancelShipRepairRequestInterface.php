<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

interface CancelShipRepairRequestInterface
{
    public function getShipId(): int;
}
