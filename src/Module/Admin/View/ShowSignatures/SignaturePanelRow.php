<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

class SignaturePanelRow
{
    private $entries = [];

    public function addEntry(&$data)
    {
        $this->entries[] = $data;
    }

    public function getEntries()
    {
        return $this->entries;
    }
}
