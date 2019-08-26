<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildResult;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildResultRequest implements ShowBuildResultRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

}