<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRepair;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipRepairRequest implements ShowShipRepairRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
