<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeleteFleet;

interface DeleteFleetRequestInterface
{
    public function getShipId(): int;
}
