<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class SubspaceData extends AbstractData
{
    #[Column(type: 'integer')]
    private int $d1c = 0;
    #[Column(type: 'integer')]
    private int $d2c = 0;
    #[Column(type: 'integer')]
    private int $d3c = 0;
    #[Column(type: 'integer')]
    private int $d4c = 0;

    public function getDirection1Count(): int
    {
        return $this->d1c;
    }

    public function getDirection2Count(): int
    {
        return $this->d2c;
    }

    public function getDirection3Count(): int
    {
        return $this->d3c;
    }

    public function getDirection4Count(): int
    {
        return $this->d4c;
    }
}
