<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildingRequest implements ShowBuildingRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getBuildingId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

}