<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CategoryRequest implements CategoryRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCategoryId(): int
    {
        return $this->parameter('cat')->int()->required();
    }
}
