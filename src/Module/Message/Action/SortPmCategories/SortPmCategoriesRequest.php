<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SortPmCategories;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SortPmCategoriesRequest implements SortPmCategoriesRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCategoryIds(): array
    {
        return $this->queryParameter('catlist')->commaSeparated()->int()->required();
    }
}
