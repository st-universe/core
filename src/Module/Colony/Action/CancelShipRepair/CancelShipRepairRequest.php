<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelShipRepairRequest implements CancelShipRepairRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getShipId(): int
    {
        return $this->parameter('shipid')->int()->required();
    }
}
