<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowColonyRequest implements ShowColonyRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
