<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

abstract class CellData
{
    private ?string $subspaceCode;
    private ?string $displayCount;

    public function __construct(
        ?string $subspaceCode,
        ?string $displayCount
    ) {
        $this->subspaceCode = $subspaceCode;
        $this->displayCount = $displayCount;
    }

    public function getSubspaceCode(): ?string
    {
        return $this->subspaceCode;
    }

    public function getDisplayCount(): ?string
    {
        return $this->displayCount;
    }
}
