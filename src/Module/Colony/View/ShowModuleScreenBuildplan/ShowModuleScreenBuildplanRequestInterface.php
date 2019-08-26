<?php

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

interface ShowModuleScreenBuildplanRequestInterface
{
    public function getColonyId(): int;

    public function getBuildplanId(): int;
}