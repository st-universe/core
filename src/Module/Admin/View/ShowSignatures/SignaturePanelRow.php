<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

class SignaturePanelRow
{

    private $entries = array();

    function addEntry(&$data)
    {
        $this->entries[] = $data;
    }

    function getEntries()
    {
        return $this->entries;
    }
}
