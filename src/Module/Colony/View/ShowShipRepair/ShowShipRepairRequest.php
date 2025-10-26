<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRepair;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipRepairRequest implements ShowShipRepairRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
