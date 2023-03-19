<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowModuleFabRequest implements ShowModuleFabRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
