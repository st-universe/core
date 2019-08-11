<?php

declare(strict_types=1);

class UserYRow extends YRow
{

    function __construct($cury, $minx, $maxx, $systemId = 0)
    {
        parent::__construct($cury, $minx, $maxx, $systemId);
    }

    function getFields()
    {
        if ($this->fields === null) {
            $this->fields = MapField::getUserFieldsByRange($this->minx, $this->maxx, $this->row);
        }
        return $this->fields;
    }

    function getSystemFields()
    {
        if ($this->fields === null) {
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $this->fields[] = SystemMap::getUserFieldByCoords($this->systemId, $i, $this->row);
            }
        }
        return $this->fields;
    }
}