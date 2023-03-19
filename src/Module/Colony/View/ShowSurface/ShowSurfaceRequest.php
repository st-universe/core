<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSurface;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSurfaceRequest implements ShowSurfaceRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
