<?php

namespace Stu\Module\Colony\View\ShowBuilding;

interface ShowBuildingRequestInterface
{
    public function getColonyId(): int;

    public function getBuildingId(): int;
}
