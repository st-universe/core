<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenu;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildMenuRequest implements ShowBuildMenuRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
