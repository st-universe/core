<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeleteFleet;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteFleetRequest implements DeleteFleetRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getShipId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
