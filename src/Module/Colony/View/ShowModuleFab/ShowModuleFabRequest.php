<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowModuleFabRequest implements ShowModuleFabRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
