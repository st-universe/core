<?php

namespace Stu\Module\Communication\View\ShowPmCategory;

interface ShowPmCategoryRequestInterface
{
    public function getListOffset(): int;

    public function getCategoryId(): int;
}