<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AbstractData implements CellDataInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $x = 0;

    #[Id]
    #[Column(type: 'integer')]
    private int $y = 0;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getPosX(): int
    {
        return $this->x;
    }

    public function getPosY(): int
    {
        return $this->y;
    }
}
