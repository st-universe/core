<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

interface StartEmergencyRequestInterface
{
    public function getEmergencyText(): string;

    public function getShipId(): int;
}
