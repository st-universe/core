<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

interface SalvageEmergencyPodsRequestInterface
{
    public function getShipId(): int;

    public function getTargetId(): int;
}
