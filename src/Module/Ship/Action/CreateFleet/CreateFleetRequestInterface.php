<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

interface CreateFleetRequestInterface
{
    public function getShipId(): int;
}
