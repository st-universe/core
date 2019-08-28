<?php

declare(strict_types=1);

class UserYRow extends YRow
{
    private $user;

    function __construct(UserData $user, $cury, $minx, $maxx, $systemId = 0)
    {
        parent::__construct($cury, $minx, $maxx, $systemId);
        $this->user = $user;
    }

    function getFields()
    {
        if ($this->fields === null) {
            $this->fields = MapField::getUserFieldsByRange($this->user, $this->minx, $this->maxx, $this->row);
        }
        return $this->fields;
    }
}