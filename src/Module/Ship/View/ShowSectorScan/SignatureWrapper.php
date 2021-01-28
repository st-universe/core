<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

class SignatureWrapper
{

    private $signature = null;

    function __construct($signature)
    {
        $this->signature = $signature;
    }

    function getRump()
    {
        if ($this->signature->getTime() > (time() - 43200)) {
            return $this->signature->getRump()->getId();
        } else {
            return null;
        }
    }

    function getShipName()
    {
        if ($this->signature->getTime() > (time() - 21600)) {
            return $this->signature->getShipName();
        } else {
            return null;
        }
    }

    function getAge()
    {
        return '~';
    }
}
