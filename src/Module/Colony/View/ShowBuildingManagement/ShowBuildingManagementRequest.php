<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildingManagement;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildingManagementRequest implements ShowBuildingManagementRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
