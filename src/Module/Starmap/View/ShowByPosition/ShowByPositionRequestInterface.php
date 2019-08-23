<?php

namespace Stu\Module\Starmap\View\ShowByPosition;

interface ShowByPositionRequestInterface
{
    public function getXCoordinate(): int;

    public function getYCoordinate(): int;
}