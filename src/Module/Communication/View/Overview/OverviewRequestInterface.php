<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\Overview;

interface OverviewRequestInterface
{
    public function getKnOffset(): int;

    public function startAtUserMark(): bool;
}
