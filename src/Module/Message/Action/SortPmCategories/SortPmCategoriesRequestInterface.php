<?php

namespace Stu\Module\Message\Action\SortPmCategories;

interface SortPmCategoriesRequestInterface
{
    /** @return array<int> */
    public function getCategoryIds(): array;
}
