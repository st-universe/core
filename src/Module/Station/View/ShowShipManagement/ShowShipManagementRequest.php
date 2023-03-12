<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipManagement;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipManagementRequest implements ShowShipManagementRequestInterface
{
    use CustomControllerHelperTrait;

    public function getStationId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
