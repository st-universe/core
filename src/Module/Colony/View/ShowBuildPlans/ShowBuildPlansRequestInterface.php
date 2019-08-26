<?php

namespace Stu\Module\Colony\View\ShowBuildPlans;

interface ShowBuildPlansRequestInterface
{
    public function getColonyId(): int;

    public function getBuildingFunctionId(): int;
}