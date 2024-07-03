<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\ShowSystemEditField;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSystemEditFieldRequest implements ShowSystemEditFieldRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFieldId(): int
    {
        return $this->queryParameter('field')->int()->required();
    }
}
