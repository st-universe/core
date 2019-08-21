<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class JoinFleetRequest implements JoinFleetRequestInterface
{
    use CustomControllerHelperTrait;

    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getFleetId(): int
    {
        return $this->queryParameter('fleetid')->int()->required();
    }
}