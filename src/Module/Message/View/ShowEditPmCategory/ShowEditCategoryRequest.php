<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowEditPmCategory;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowEditCategoryRequest implements ShowEditCategoryRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCategoryId(): int
    {
        return $this->parameter('pmcat')->int()->defaultsTo(0);
    }
}
