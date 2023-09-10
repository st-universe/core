<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

class MapCellData extends CellData
{
    private ?string $mapGraphicPath;

    public function __construct(
        ?string $mapGraphicPath,
        ?string $subspaceCode,
        ?string $displayCount
    ) {
        parent::__construct($subspaceCode, $displayCount);

        $this->mapGraphicPath = $mapGraphicPath;
    }

    public function getMapGraphicPath(): ?string
    {
        return $this->mapGraphicPath;
    }
}
