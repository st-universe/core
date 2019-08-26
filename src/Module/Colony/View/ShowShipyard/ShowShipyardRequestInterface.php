<?php

namespace Stu\Module\Colony\View\ShowShipyard;

interface ShowShipyardRequestInterface
{
    public function getColonyId(): int;

    public function getBuildingFunctionId(): int;
}