<?php

declare(strict_types=1);

class VisualNavPanelRow
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