<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

interface LeaveFleetRequestInterface
{
    public function getShipId(): int;
}
