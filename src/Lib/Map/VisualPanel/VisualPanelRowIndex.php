<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

class VisualPanelRowIndex implements VisualPanelElementInterface
{
    private int $row;

    private string $cssClass;

    public function __construct(
        int $row,
        string $cssClass
    ) {
        $this->row = $row;
        $this->cssClass = $cssClass;
    }

    public function isRowIndex(): bool
    {
        return true;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getCSSClass(): string
    {
        return $this->cssClass;
    }
}
