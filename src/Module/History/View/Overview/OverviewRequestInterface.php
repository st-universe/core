<?php

namespace Stu\Module\History\View\Overview;

interface OverviewRequestInterface
{
    public function getTypeId(array $possibleTypeIds, int $default): int;

    public function getCount(int $default): int;
}
