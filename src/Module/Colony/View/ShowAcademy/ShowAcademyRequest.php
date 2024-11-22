<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowAcademyRequest implements ShowAcademyRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
