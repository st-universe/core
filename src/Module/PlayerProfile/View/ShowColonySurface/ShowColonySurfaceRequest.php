<?php

declare(strict_types=1);

namespace Stu\Module\Profile\View\ShowColonySurface;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowColonySurfaceRequest implements ShowColonySurfaceRequestInterface
{
    use CustomControllerHelperTrait;

    public function getId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
