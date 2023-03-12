<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

interface StopEmergencyRequestInterface
{
    public function getShipId(): int;
}
