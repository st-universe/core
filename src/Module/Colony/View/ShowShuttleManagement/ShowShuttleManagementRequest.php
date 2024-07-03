<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShuttleManagement;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShuttleManagementRequest implements ShowShuttleManagementRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('entity')->int()->required();
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->queryParameter('ship')->int()->required();
    }
}
