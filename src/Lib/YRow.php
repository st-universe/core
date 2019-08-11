<?php

declare(strict_types=1);

class YRow
{

    protected $row = null;
    protected $minx = null;
    protected $maxx = null;
    protected $systemId = null;

    function __construct($cury, $minx, $maxx, $systemId = 0)
    {
        $this->row = $cury;
        $this->minx = $minx;
        $this->maxx = $maxx;
        $this->systemId = $systemId;
    }

    protected $fields = null;

    function getFields()
    {
        if ($this->fields === null) {
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $this->fields[] = MapField::getFieldByCoords($i, $this->row);
            }
        }
        return $this->fields;
    }

    function getSystemFields()
    {
        if ($this->fields === null) {
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $this->fields[] = SystemMap::getFieldByCoords($this->systemId, $i, $this->row);
            }
        }
        return $this->fields;
    }

    function getRow()
    {
        return $this->row;
    }
}