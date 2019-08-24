<?php

namespace Stu\Module\Communication\Action\SortPmCategories;

interface SortPmCategoriesRequestInterface
{
    public function getCategoryIds(): array;
}