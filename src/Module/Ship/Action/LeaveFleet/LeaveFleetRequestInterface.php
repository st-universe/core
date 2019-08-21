<?php

namespace Stu\Module\Ship\Action\LeaveFleet;

interface LeaveFleetRequestInterface
{
    public function getShipId(): int;
}