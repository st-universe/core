<?php

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Orm\Entity\LayerInterface;

interface ShowSectionRequestInterface
{
    public function getLayer(): LayerInterface;

    public function getXCoordinate(): int;

    public function getYCoordinate(): int;

    public function getSectionId(): int;
}
