<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

interface OverviewRequestInterface
{
    public function getPlayerId(): int;
}