<?php

namespace Stu\Module\Colony\View\ShowModuleScreen;

interface ShowModuleScreenRequestInterface
{
    public function getColonyId(): int;

    public function getRumpId(): int;
}
