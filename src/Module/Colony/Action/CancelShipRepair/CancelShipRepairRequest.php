<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelShipRepairRequest implements CancelShipRepairRequestInterface
{
    use CustomControllerHelperTrait;

    public function getShipId(): int
    {
        return $this->queryParameter('shipid')->int()->required();
    }
}
