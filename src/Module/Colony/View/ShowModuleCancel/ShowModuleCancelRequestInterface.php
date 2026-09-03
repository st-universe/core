<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleCancel;

interface ShowModuleCancelRequestInterface
{
    public function getColonyId(): int;

    public function getModuleId(): int;
}
