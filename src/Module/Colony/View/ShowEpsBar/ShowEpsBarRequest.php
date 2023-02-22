<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowEpsBar;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowEpsBarRequest implements ShowEpsBarRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
