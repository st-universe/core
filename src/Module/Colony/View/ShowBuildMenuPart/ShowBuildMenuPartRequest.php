<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenuPart;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildMenuPartRequest implements ShowBuildMenuPartRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

}