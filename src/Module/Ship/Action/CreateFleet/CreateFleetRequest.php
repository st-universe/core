<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateFleetRequest implements CreateFleetRequestInterface
{
    use CustomControllerHelperTrait;

    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}