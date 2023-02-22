<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

class VisualNavPanelRow
{
    /** @var array<VisualNavPanelEntry>  */
    private array $entries = [];

    public function addEntry(VisualNavPanelEntry $data): void
    {
        $this->entries[] = $data;
    }

    /**
     * @return array<VisualNavPanelEntry>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
