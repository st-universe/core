<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowModuleScreenRequest implements ShowModuleScreenRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getRumpId(): int
    {
        return $this->queryParameter('rump')->int()->required();
    }
}
