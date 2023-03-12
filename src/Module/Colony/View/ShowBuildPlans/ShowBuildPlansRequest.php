<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildPlansRequest implements ShowBuildPlansRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getBuildingFunctionId(): int
    {
        return $this->queryParameter('func')->int()->required();
    }
}