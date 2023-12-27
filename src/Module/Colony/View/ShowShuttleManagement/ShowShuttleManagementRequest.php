<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShuttleManagement;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShuttleManagementRequest implements ShowShuttleManagementRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('entity')->int()->required();
    }

    public function getShipId(): int
    {
        return $this->queryParameter('ship')->int()->required();
    }
}
