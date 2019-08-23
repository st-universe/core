<?php

namespace Stu\Module\Starmap\View\ShowSection;

interface ShowSectionRequestInterface
{
    public function getXCoordinate(): int;

    public function getYCoordinate(): int;

    public function getSectionId(): int;
}