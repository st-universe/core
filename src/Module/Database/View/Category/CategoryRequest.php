<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CategoryRequest implements CategoryRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCategoryId(): int
    {
        return $this->queryParameter('cat')->int()->required();
    }
}
