<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowModuleScreenRequest implements ShowModuleScreenRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->queryParameter('rump')->int()->required();
    }
}
