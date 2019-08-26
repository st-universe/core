<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenu;

interface ShowBuildMenuRequestInterface
{
    public function getColonyId(): int;
}