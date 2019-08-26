<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowEpsBar;

interface ShowEpsBarRequestInterface
{
    public function getColonyId(): int;
}