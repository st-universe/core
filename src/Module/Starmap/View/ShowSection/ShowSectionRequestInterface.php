<?php

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Orm\Entity\LayerInterface;

interface ShowSectionRequestInterface
{
    public function getLayerId(): int;

    public function getXCoordinate(LayerInterface $layer): int;

    public function getYCoordinate(LayerInterface $layer): int;

    public function getSectionId(): int;
}
