<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

class VisualPanelRow
{
    private int $y;

    /** @var array<VisualPanelElementInterface>  */
    private array $entries;

    public function __construct(int $y)
    {
        $this->y = $y;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function addEntry(VisualPanelElementInterface $element): void
    {
        $this->entries[] = $element;
    }

    /**
     * @return array<VisualPanelElementInterface>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
