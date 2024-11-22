<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitManagement;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOrbitManagementRequest implements ShowOrbitManagementRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
