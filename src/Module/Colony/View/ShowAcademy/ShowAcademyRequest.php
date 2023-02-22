<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAcademy;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowAcademyRequest implements ShowAcademyRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
