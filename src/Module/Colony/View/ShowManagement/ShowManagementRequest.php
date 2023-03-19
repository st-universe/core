<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowManagement;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowManagementRequest implements ShowManagementRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
