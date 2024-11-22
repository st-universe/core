<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShuttleManagement;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShuttleManagementRequest implements ShowShuttleManagementRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getStationId(): int
    {
        return $this->parameter('entity')->int()->required();
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->parameter('ship')->int()->required();
    }
}
