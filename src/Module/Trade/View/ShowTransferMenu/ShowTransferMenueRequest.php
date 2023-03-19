<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTransferMenu;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTransferMenueRequest implements ShowTransferMenueRequestInterface
{
    use CustomControllerHelperTrait;

    public function getStorageId(): int
    {
        return $this->queryParameter('storid')->int()->required();
    }
}
