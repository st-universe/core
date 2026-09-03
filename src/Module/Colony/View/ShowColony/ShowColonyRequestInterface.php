<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

interface ShowColonyRequestInterface
{
    public function getColonyId(): int;
}
