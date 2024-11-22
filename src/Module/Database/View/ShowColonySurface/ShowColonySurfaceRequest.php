<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowColonySurface;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowColonySurfaceRequest implements ShowColonySurfaceRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
