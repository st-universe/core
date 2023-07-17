<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

class SignaturePanelRow
{
    private array $entries = [];

    public function addEntry(&$data): void
    {
        $this->entries[] = $data;
    }

    public function getEntries()
    {
        return $this->entries;
    }
}
