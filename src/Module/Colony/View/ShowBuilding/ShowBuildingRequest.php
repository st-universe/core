<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildingRequest implements ShowBuildingRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getBuildingId(): int
    {
        return $this->parameter('buildingid')->int()->required();
    }
}
