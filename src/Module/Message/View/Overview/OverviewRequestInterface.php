<?php

namespace Stu\Module\Message\View\Overview;

interface OverviewRequestInterface
{
    public function getListOffset(): int;

    public function getCategoryId(): int;
}
