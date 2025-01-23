<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTransferMenu;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTransferMenueRequest implements ShowTransferMenueRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getStorageId(): int
    {
        return $this->parameter('storid')->int()->required();
    }
}
