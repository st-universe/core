<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SortPmCategories;

use Stu\Lib\Request\CustomControllerHelperTrait;
use Stu\Module\Message\Action\SortPmCategories\SortPmCategoriesRequestInterface;

final class SortPmCategoriesRequest implements SortPmCategoriesRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCategoryIds(): array
    {
        return $this->queryParameter('catlist')->commaSeparated()->int()->required();
    }
}
