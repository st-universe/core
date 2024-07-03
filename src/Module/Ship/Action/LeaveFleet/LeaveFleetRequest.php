<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class LeaveFleetRequest implements LeaveFleetRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
