<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowModuleScreenBuildplanRequest implements ShowModuleScreenBuildplanRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getBuildplanId(): int
    {
        return $this->queryParameter('planid')->int()->required();
    }
}