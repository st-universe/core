<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\ShowSystem;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSystemRequest implements ShowSystemRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getSystemId(): int
    {
        return $this->parameter('systemid')->int()->required();
    }
}
