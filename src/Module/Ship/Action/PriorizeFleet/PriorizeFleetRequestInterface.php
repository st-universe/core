<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\PriorizeFleet;

interface PriorizeFleetRequestInterface
{
    public function getFleetId(): int;
}
