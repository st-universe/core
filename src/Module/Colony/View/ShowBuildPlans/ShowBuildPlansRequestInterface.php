<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

interface ShowBuildPlansRequestInterface
{
    public function getColonyId(): int;
}
