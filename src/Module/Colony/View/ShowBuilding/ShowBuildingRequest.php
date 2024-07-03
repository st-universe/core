<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildingRequest implements ShowBuildingRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getBuildingId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }
}
