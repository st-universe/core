<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartEmergency;

interface StartEmergencyRequestInterface
{
    public function getEmergencyText(): string;

    public function getShipId(): int;
}
